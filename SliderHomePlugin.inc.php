<?php
/**
 * @file plugins/generic/home/HomePlugin.inc.php
 *
 * Copyright (c) 2021 Universitätsbibliothek Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 */

// TODO @ RS namespace APP\plugins\generic\sliderHome;

// import('lib.pkp.classes.plugins.GenericPlugin');
import('plugins.generic.sliderHome.controllers.grid.form.SliderContentForm');
import('plugins.generic.sliderHome.classes.components.form.SliderContentForm');
import('plugins.generic.sliderHome.classes.components.SliderHomeListPanel');
import('plugins.generic.sliderHome.controllers.tab.SliderHomeSettingsTabFormHandler');
import('plugins.generic.sliderHome.controllers.components.SliderHomeFormHandler');
use PKP\plugins\GenericPlugin;

/**
 * @class SliderHomePlugin
 * 
 * @brief Enables display of image slider on the journal/press home page.
 */
class SliderHomePlugin extends GenericPlugin {

	protected $_endpointsSetup = false;
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

		$handler = new SliderHomeSettingsTabFormHandler();

		// add the new endpoint
		$endpoints['POST'][] = 
			[
				'pattern' => '/{contextPath}/api/{version}/contexts/{contextId}/sliderSettings',
				'handler' => [$handler, 'saveFormData'],
				'roles' => array(ROLE_ID_SITE_ADMIN, ROLE_ID_MANAGER)
			];

		// regsiter new FormComponent endpoints
		$handler = new SliderHomeFormHandler();
		$endpoints = array_merge($endpoints, $handler->setupEndpoints());
	}
	
	// OMP/OJS 3.2: Add tab for slider content grid in website settings appearance
	function callbackAppearanceTab($hookName, $args) {		

		# prepare data
		$templateMgr =& $args[1];
		$output =& $args[2];
		$request =& Registry::get('request');
		$context = $request->getContext();
		$contextId = $context->getId();
		$dispatcher = $request->getDispatcher();

		$supportedFormLocales = $context->getSupportedFormLocales();
		$localeNames = \PKP\facades\Locale::getLocales();
		$locales = array_map(function($localeKey) use ($localeNames) {
			return ['key' => $localeKey, 'label' => $localeNames[$localeKey]->locale];
		}, $supportedFormLocales);
		$formLocaleNames = $context->getSupportedFormLocaleNames(); // TODO @RS

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

		// instantinate settings form
		$this->import('classes.components.form.context.SliderHomeSettingsForm');
		$sliderSettingsForm = new SliderHomeSettingsForm($contextApiUrl, $locales, $context, $baseUrl, $temporaryFileApiUrl, $publicFileApiUrl, $contextUrl,
			['maxHeight' => $maxHeight,
				'speed' => $speed,
				'delay' => $delay,
				'stopOnLastSlide' => $this->getSetting($contextId, 'stopOnLastSlide'),
				'fallbackLocale' => $this->getSetting($contextId, 'fallbackLocale')?:"usePrimary"
			]
		);

		// get slider data
		import('plugins.generic.sliderHome.classes.SliderHomeDAO');
		$sliderHomeDao = new SliderHomeDao();
		$sliderImages = array_map(
			function ($item) {
				return [
					'id' => $item->getData('id'),
					'name' => $item->getData('name'),
					'show_content' => $item->getData('showContent')
				];
			},
			$sliderHomeDao->getByContextId($contextId)->toArray()
		);

		// get slider content form
		$sliderContentForm = new SliderContentForm_NEW($contextApiUrl, $formLocaleNames, $context, $baseUrl, $temporaryFileApiUrl, $publicFileApiUrl, $contextUrl);

		// get SliderHomeListPanel
		//http://localhost:50020/ojs/index.php/dja/$$$call$$$/plugins/generic/slider-home/controllers/grid/slider-home-grid/delete?sliderContentId=2
		$apiUrl = $dispatcher->url(
			$request,
			PKPApplication::ROUTE_COMPONENT,
			null,
			'plugins.generic.sliderHome.controllers.grid.SliderHomeGridHandler',
			null,
			null,
			null
		);
		$apiUrl = $dispatcher->url(
			$request,
			ROUTE_API,
			$context->getPath(),
			"contexts/" . $contextId . "/sliderHome"
		);
		$sliderHomeListPanel = new SliderHomeListPanel(
            FORM_SLIDER_LIST_PANEL,
            __('plugins.generic.sliderHome.gridTitle'),
            [
                'apiUrl' => $apiUrl,
                'form' => $sliderContentForm,
                'items' => $sliderImages,
            ]
        );

		# setup template, this allows us to use the constants in the tpl-file
		$templateMgr->setConstants([
			'FORM_SLIDER_SETTINGS' => FORM_SLIDER_SETTINGS,
			'FORM_SLIDER_CONTENT' => FORM_SLIDER_CONTENT_NEW,
			'FORM_SLIDER_LIST_PANEL' => FORM_SLIDER_LIST_PANEL
		]); 
		
		// In OJS 3.3 $templateMgr->setState doesn't seem to update template vars anymore
		// The $templateMgr Object provided by $args differs from the one provided by TemplateManager::getManager($request)
		// $templateMgr->setState([
        //     'components' => [
        //         	FORM_SLIDER_SETTINGS => $sliderSettingsForm->getConfig(),
		// 			FORM_SLIDER_CONTENT => $sliderContentForm->getConfig(),
		// 			FORM_SLIDER_LIST_PANEL => $sliderHomeListPanel->getConfig()
        //     ],
        // ]);

		// set state
		$state = $templateMgr->getTemplateVars('state');
		$state['components'][FORM_SLIDER_SETTINGS] = $sliderSettingsForm->getConfig();
		$state['components'][FORM_SLIDER_CONTENT_NEW] = $sliderContentForm->getConfig();
		$state['components'][FORM_SLIDER_LIST_PANEL] = $sliderHomeListPanel->getConfig();
		$templateMgr->assign('state', $state);

		// render template
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
			case 'management/website.tpl':
				$templateMgr->addJavaScript(
					'sliderHomeJS',
					"{$request->getBaseUrl()}/{$this->getPluginPath()}/public/build/build.iife.js",
					[
						'inline' => false,
						'contexts' => ['backend'],
						'priority' => STYLE_SEQUENCE_LAST
					]
				);
				$templateMgr->addStyleSheet('sliderHomeListPanelStyle',"{$request->getBaseUrl()}/{$this->getPluginPath()}/public/build/style.css", [
					'contexts' => ['backend']
				] );		
		}
		return false;
	}
	
	private function addHeader($templateMgr,$baseUrl) {
		$templateMgr->addHeader(
			'slider',
			"<link rel='stylesheet' href='".$baseUrl."/plugins/generic/sliderHome/swiper/css/sliderHome.css'>"
		);
		$templateMgr->addHeader(
			'swiper-min',
			"<link rel='stylesheet' href='".$baseUrl."/plugins/generic/sliderHome/swiper/css/swiper-bundle.min.css'>"
		);		
		$templateMgr->addHeader(
			'swiper-min-js',
			"<script src='".$baseUrl."/plugins/generic/sliderHome/swiper/js/swiper-bundle.min.js'></script>"
		);
	}
	
	// get markup for slider content, incl. containers/wrappers
	private function getSliderContent($request) {

		$templateMgr = TemplateManager::getManager($request);
		$locale = $templateMgr->getTemplateVars('currentLocale'); 
		$context = $request->getContext();
		$primaryLocale = $context->getPrimaryLocale();
		$contextPath = get_class($context) === 'Press'?'/presses/':'/journals/';
		$contextId = $context->getId();
		$maxHeight = $this->getSetting($contextId, 'maxHeight');
		$speed = $this->getSetting($contextId, 'speed');
		$delay = $this->getSetting($contextId, 'delay');
		$stopOnLastSlide = $this->getSetting($contextId, 'stopOnLastSlide')?"true":"false";
		$fallbackLocale = $this->getSetting($contextId, 'fallbackLocale')?:"usePrimary";

		import('plugins.generic.sliderHome.classes.SliderHomeDAO');
		$sliderHomeDao = new SliderHomeDao();

		# get slider content based on locale to show
		if ($fallbackLocale =='usePrimary') {
			$contentArrayCurrentLocale = $sliderHomeDao->getAllContent($contextId, $locale);
			$contentArrayPrimaryLocale = $sliderHomeDao->getAllContent($contextId, $primaryLocale);
			$localizedFields = $sliderHomeDao->getLocaleFieldNames();
			$contentArray = array_map(
				function ($current, $primary) use ($localizedFields) {
					foreach ($localizedFields as $field) {
						$current[$field] = $current[$field]==""?$primary[$field]:$current[$field];
					}
					return $current;
				},
				$contentArrayCurrentLocale,
				$contentArrayPrimaryLocale
			);
		} else {
			$contentArray = $sliderHomeDao->getAllContent($contextId, $locale);
		};
		
		$sliderContent = "";

		if (!empty($contentArray)) {
			$sliderContent="<div class='swiper-container'><div class='swiper-wrapper'>";
			foreach ($contentArray as $value) {

				$contentHTML = new DOMDocument();

				// get text content of slide
				if ($value->content) {
					$contentHTML->loadHTML('<?xml encoding="utf-8" ?><div id="slider-text" class="slider-text">'.$value->content.'</div>');
				}
				
				// create slide tag
				$slide = $contentHTML->createElement('div');
				$slide->setAttribute("class", "swiper-slide");

				// create slider fiure and image tag
				// figure
				$sliderFigure = $contentHTML->createElement("figure");
				
				$baseUrl = Config::getVar('general', 'base_url');
				$publicFilesDir = Config::getVar('files', 'public_files_dir');

				// image
				$sliderImg = $contentHTML->createElement('img');
				$sliderImg->setAttribute("style", "max-height:".$maxHeight."vh");
				$sliderImg->setAttribute("src", $baseUrl.'/'.$publicFilesDir.$contextPath.$contextId.'/'.$value['sliderImage']);
				$sliderImg->setAttribute("alt", $value['sliderImageAltText']);

				// image link
				if ($value['sliderImageLink']) {
					$sliderImgLink = $contentHTML->createElement('a');
					$sliderImgLink->setAttribute("href", $value['sliderImageLink']);
					$sliderImgLink->setAttribute("class", 'slider-link');
					$sliderImgLink->appendChild($sliderImg);
					$sliderFigure->appendChild($sliderImgLink);
				} else {
					$sliderFigure->appendChild($sliderImg);
				}				
				
				if ($value['copyright']) {
					$smallTag = $contentHTML->createElement("small", $value['copyright']);
					$smallTag->setAttribute('class',"slider-copyright");
					$sliderFigure->appendChild($smallTag);
				}

				// append overlay content to figure tag
				if ($value['content']) {

					if (str_contains($value['content'], 'href')) {
						$noclick = '';
					} else {
						$nocklick = ' noclick';
					}

					$overlayContent = $contentHTML->createElement("div");
					// copy all content tags
					foreach ($contentHTML->getElementsByTagName('body')[0]->childNodes as $node) {
						$overlayContent->appendChild($node);
					}
					$overlayContent->setAttribute('class',"slider-text".$noclick);
					$sliderFigure->appendChild($overlayContent);
				}

				// append slider image to slide tag
				$slide->appendChild($sliderFigure);

				// generate output HTML
				$sliderContent.= $contentHTML->saveHTML($slide);

			}
			// add slider navigation 
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
	 * Set up handler TODO @RS rename
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
	 * @copydoc Plugin::getInstallMigration()
	 */
	function getInstallMigration() {
		$this->import('SliderHomeSchemaMigration');
		return new SliderHomeSchemaMigration();
	}
}

?>
