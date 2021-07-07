<?php
/**
 * @file plugins/generic/home/HomePlugin.inc.php
 *
 * Copyright (c) 2021 Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

/**
 * @class SliderHomePlugin
 * 
 * @brief Enables display of image slider on the journal home page.
 */
class SliderHomePlugin extends GenericPlugin {
	/**
	 * Register the plugin.
	 * @param $category string
	 * @param $path string
	 */
	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path, $mainContextId = null) {			
		if (parent::register($category, $path, $mainContextId)) {
			if ($this->getEnabled($mainContextId)) {
				HookRegistry::register('TemplateManager::display',array($this, 'callbackDisplay')); //to enable slider display in OMP frontend
				HookRegistry::register('Template::Settings::website::appearance', array($this, 'callbackAppearanceTab')); //to enable display of plugin settings tab
				HookRegistry::register('LoadComponentHandler', array($this, 'setupGridHandler')); //to load (old style) grid handler for image uploadd form
				HookRegistry::register('Templates::Index::journal', array($this, 'callbackIndexJournal')); //to enable slider display in OJS frontend
				HookRegistry::register('APIHandler::endpoints', array($this, 'callbackSetupEndpoints')); //to setup endpoint for ComponentForm submission via REST API
			}
			return true;
		}
		return false;
	}

	function callbackSetupEndpoints($hook, $args) {
		$endpoints =& $args[0];

		import('plugins.generic.sliderHome.controllers.tab.SliderHomeSettingsTabFormHandler');
		$handler = new SliderHomeSettingsTabFormHandler();

		// add the new endpoint
		$endpoints['POST'][] = 
			[
				'pattern' => '/{contextPath}/api/{version}/contexts/{contextId}/sliderSettings',
				'handler' => [$handler, 'saveFormData'],
				'roles' => array(ROLE_ID_SITE_ADMIN, ROLE_ID_MANAGER)
			];
	}
	
	// OMP/OJS 3.2: Add tab for slider content grid in website settings appearance
	function callbackAppearanceTab($hookName, $args) {		

		# prepare data
		$templateMgr = $args[1];
		$output =& $args[2];
		$request =& Registry::get('request');
		$context = $request->getContext();
		$contextId = $context->getId();
		$dispatcher = $request->getDispatcher();

		$supportedFormLocales = $context->getSupportedFormLocales();
		$localeNames = AppLocale::getAllLocales();
		$locales = array_map(function($localeKey) use ($localeNames) {
			return ['key' => $localeKey, 'label' => $localeNames[$localeKey]];
		}, $supportedFormLocales);

		import('classes.file.PublicFileManager');
		$publicFileManager = new PublicFileManager();
		$baseUrl = $request->getBaseUrl() . '/' . $publicFileManager->getContextFilesPath($context->getId());
		$temporaryFileApiUrl = $dispatcher->url($request, ROUTE_API, $context->getPath(), 'temporaryFiles');
		$publicFileApiUrl = $dispatcher->url($request, ROUTE_API, $context->getPath(), '_uploadPublicFile');
		$contextApiUrl = $dispatcher->url(
			$request,
			ROUTE_API,
			$context->getPath(),
			'contexts/' . $context->getId() . "/sliderSettings"
		);
		$contextUrl = $request->getRouter()->url($request, $context->getPath());

		# get data to initilaize ComponentForm 
		$maxHeight = $this->getSetting($contextId, 'maxHeight');
		if (!$maxHeight) { 
			// set default value
			$maxHeight = 100;
			$this->updateSetting($contextId, 'maxHeight', $maxHeight, $type = null, $isLocalized = false);
		}
		$speed = $this->getSetting($contextId, 'speed');
		if (!$speed) { 
			// set default value
			$speed = 2000;
			$this->updateSetting($contextId, 'speed', $speed, $type = null, $isLocalized = false);
		}
		$delay = $this->getSetting($contextId, 'delay');
		if (!$delay) {
			// set default value
			$delay = 2000;
			$this->updateSetting($contextId, 'delay', $delay, $type = null, $isLocalized = false);
		}
		$stopOnLastSlide = $this->getSetting($contextId, 'stopOnLastSlide');

		// instantinate settings form
		$this->import('classes.components.form.context.SliderHomeSettingsForm');
		$sliderSettingsForm = new SliderHomeSettingsForm($contextApiUrl, $locales, $context, $baseUrl, $temporaryFileApiUrl, $publicFileApiUrl, $contextUrl,
			['maxHeight' => $maxHeight,
				'speed' => $speed,
				'delay' => $delay,
				'stopOnLastSlide' => $stopOnLastSlide
			]
		);

		# setup template
		$settingsData = $templateMgr->getTemplateVars('settingsData');
		$settingsData['components'] += [FORM_SLIDER_SETTINGS  => $sliderSettingsForm->getConfig()];
		$templateMgr->assign('settingsData', $settingsData);

		$templateMgr->setConstants([
			'FORM_SLIDER_SETTINGS',
		]);
		$output .= $templateMgr->fetch($this->getTemplateResource('appearanceTab.tpl'));

		// Permit other plugins to continue interacting with this hook
		return false;
	}

	// OJS: there's a template hook on the frontend journal index page
	function callbackIndexJournal($hookName, $args) {	
		$request = $this->getRequest();

		$output =& $args[2];
		$output .= $this->getSliderContent($request);

		return false;
	}	
		
	// OMP: no template hook on the index template -> use display hook to replace template
	function callbackDisplay($hookName, $args) {
		$request = $this->getRequest();
		$templateMgr =& $args[0];
		$template =& $args[1];
		$applicationName = PKPApplication::get()->getName();		
		switch ($template) {
			case 'frontend/pages/index.tpl':
				if ($applicationName=="omp") {
					$sliderContent = $this->getSliderContent($request);
					$templateMgr->assign('sliderContent',$sliderContent);
					$this->addHeader($templateMgr,$request->getBaseUrl());
					$templateMgr->display($this->getTemplateResource('homeOMP.tpl'));
					return true;					
				}
			case 'frontend/pages/indexJournal.tpl':
				$this->addHeader($templateMgr,$request->getBaseUrl());
		}
		return false;
	}
	
	private function addHeader($templateMgr,$baseUrl) {
		$templateMgr->addHeader(
			'slider',
			"<link rel='stylesheet' href='".$baseUrl."/plugins/generic/sliderHome/swiper/css/sliderHome.css'>"
		);
		$templateMgr->addHeader(
			'swiper',
			"<link rel='stylesheet' href='".$baseUrl."/plugins/generic/sliderHome/swiper/css/swiper-bundle.css'>"
		);
		$templateMgr->addHeader(
			'swiper-min',
			"<link rel='stylesheet' href='".$baseUrl."/plugins/generic/sliderHome/swiper/css/swiper-bundle.min.css'>"
		);		
		$templateMgr->addHeader(
			'swiper-js',
			"<script src='".$baseUrl."/plugins/generic/sliderHome/swiper/js/swiper-bundle.js'></script>"
		);
		$templateMgr->addHeader(
			'swiper-min-js',
			"<script src='".$baseUrl."/plugins/generic/sliderHome/swiper/js/swiper-bundle.min.js'></script>"
		);
	}
	
	// get markup for slider content, incl. containers/wrappers
	private function getSliderContent($request) {

		$contextId = $request->getContext()->getId();
		$maxHeight = $this->getSetting($contextId, 'maxHeight');
		$speed = $this->getSetting($contextId, 'speed');
		$delay = $this->getSetting($contextId, 'delay');
		$stopOnLastSlide = $this->getSetting($contextId, 'stopOnLastSlide')?"true":"false";

		import('plugins.generic.sliderHome.classes.SliderHomeDAO');
		$sliderHomeDao = new SliderHomeDao();
		$contentArray = $sliderHomeDao->getAllContent($contextId);
		$sliderContent = "";

		if (!empty($contentArray)) {
			$sliderContent="<div class='swiper-container'><div class='swiper-wrapper'>";
			foreach ($contentArray as $value) {
				$sliderContent.= "<div class='swiper-slide'>";
				$sliderContent.= preg_replace("#<img#","<img style='max-height:".$maxHeight."vh'",$value);
				$sliderContent.= "</div>";
			}
			$sliderContent.= "</div><div class='swiper-pagination'></div><div class='swiper-button-prev'></div><div class='swiper-button-next'></div></div>";
			$sliderContent .= 
			"<script>
				var swiper = new Swiper('.swiper-container', {
					autoHeight: true, //enable auto height
					pagination: {
						el: '.swiper-pagination',
						clickable: true,
						renderBullet: function (index, className) {
							return '<span class=\"' + className + '\">' + '</span>';
						},
					},
					navigation: {
						nextEl: '.swiper-button-next',
    					prevEl: '.swiper-button-prev',
					},
					speed: ".$speed.",
					autoplay: { delay: ".$delay.",disableOnInteraction:true, stopOnLastSlide:".$stopOnLastSlide." },
				});
			</script>";
		}
		return $sliderContent;
	}	
	
	/**
	 * Set up handler
	 */
	function setupGridHandler($hookName, $params) {
		
		$component =& $params[0];
		if ($component == 'plugins.generic.sliderHome.controllers.grid.SliderHomeGridHandler') {			
			import($component);
			SliderHomeGridHandler::setPlugin($this);
			return true;
		}	
		return false;
	}	

	/**
	 * @copydoc PKPPlugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.generic.sliderHome.displayName');
	}

	/**
	 * @copydoc PKPPlugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.sliderHome.description');
	}
	
	/**
	 * Get the filename of the ADODB schema for this plugin.
	 * @return string Full path and filename to schema descriptor.
	 */
	function getInstallSchemaFile() {
		return $this->getPluginPath() . '/schema.xml';
	}
}

?>
