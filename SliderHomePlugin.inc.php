<?php

/**
 * @file plugins/generic/home/HomePlugin.inc.php
 *
 * Copyright (c) 2016 Language Science Press
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HomePlugin
 */

import('lib.pkp.classes.plugins.GenericPlugin');

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
				
				HookRegistry::register('TemplateManager::display',array($this, 'callbackDisplay'));
				HookRegistry::register('Template::Settings::website::appearance', array($this, 'callbackAppearanceTab'));
				HookRegistry::register('Templates::Management::Settings::website', array($this, 'callbackWebsiteSettingsTab'));				
				HookRegistry::register('LoadComponentHandler', array($this, 'setupGridHandler'));
				HookRegistry::register('Templates::Index::journal', array($this, 'callbackIndexJournal'));
				HookRegistry::register('slidersettingstabform::Constructor', array($this, 'callbackSliderFormConstruct'));
				
			}
			return true;
		}
		return false;
	}

	function callbackSliderFormConstruct($hookname, $args) {
		$args[0]->_setPlugin($this);
		$args[0]->_setContextId(Application::getRequest()->getContext()->getId());
	}
	
	// OJS 3.1.2: Add tab fro slider content grid in website settings
	function callbackWebsiteSettingsTab($hookName, $args) {
		$versionDao = DAORegistry::getDAO('VersionDAO');
		$currentVersion = $versionDao->getCurrentVersion();
		$version = $currentVersion->getMajor().".".$currentVersion->getMinor().".".$currentVersion->getRevision();
		$product = $currentVersion->getProduct();
		if ($product=="ojs2" && $version="3.1.2") {
			$templateMgr = $args[1];
			$output =& $args[2];
			$request =& Registry::get('request');
			$dispatcher = $request->getDispatcher();
			$output .= '<li><a name="sliderHome" href="' . $dispatcher->url(
					$request,
					ROUTE_COMPONENT,
					null,
					'plugins.generic.sliderHome.controllers.tab.SliderHomeSettingsTabFormHandler',
					'showTab',
					null,
					array('tab' => 'sliderHome')
				) . '">' . __('plugins.generic.sliderHome.tabname') . '</a></li>';
		}
		return false; // Permit other plugins to continue interacting with this hook
	}
	
	// OMP/OJs 3.2: Add tab for slider content grid in website settings appearance
	function callbackAppearanceTab($hookName, $args) {		
		$templateMgr = $args[1];
		$output =& $args[2];
		$request =& Registry::get('request');
		$dispatcher = $request->getDispatcher();
		$output .= $templateMgr->fetch($this->getTemplateResource('appearanceTab.tpl'));

		// Permit other plugins to continue interacting with this hook
		return false;
	}

	// OJS: there's a template hook on the frontend journal index page
	function callbackIndexJournal($hookName, $args) {	
		
		$contextId = Application::getRequest()->getContext()->getId();
		$speed = $this->getSetting($contextId, 'speed'); #default 2000
		$delay = $this->getSetting($contextId, 'delay'); #default 200000
		$stopOnLastSlide = $this->getSetting($contextId, 'stopOnLastSlide')?"true":"false";

		$output =& $args[2];
		$request = $this->getRequest();
		$output .= $this->getSliderContent($request);
		$output .= 
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
		return false;
	}	
		
	// OMP: no template hook on the index template -> use display hook to replace template
	function callbackDisplay($hookName, $args) {	
		$request = $this->getRequest();
		$templateMgr =& $args[0];
		$template =& $args[1];
		$applicationName = Application::getApplication()->getName();		
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
		}
		return $sliderContent;
	}	
	
	/**
	 * Set up handler
	 */
	function setupGridHandler($hookName, $params) {
		
		$component =& $params[0];		
		if ($component == 'plugins.generic.sliderHome.controllers.grid.SliderHomeGridHandler') {			
			define('SLIDERHOME_PLUGIN_NAME', $this->getName());
			import($component);
			SliderHomeGridHandler::setPlugin($this);
			return true;
		}	
		if ($component == 'plugins.generic.sliderHome.controllers.tab.SliderHomeSettingsTabFormHandler') {			
			define('SLIDERHOME_PLUGIN_NAME', $this->getName());
			import($component);
			SliderHomeSettingsTabFormHandler::setPlugin($this);
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
