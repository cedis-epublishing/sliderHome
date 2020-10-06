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
	//			HookRegistry::register('Templates::Management::Settings::website', array($this, 'callbackWebsiteSettingsTab'));				
				HookRegistry::register('LoadComponentHandler', array($this, 'setupGridHandler'));
	//			HookRegistry::register('Templates::Index::journal', array($this, 'callbackIndexJournal'));
				
			}
			return true;
		}
		return false;
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
			$output .= '<li><a name="sliderHome" href="' . $dispatcher->url($request, ROUTE_COMPONENT, null, 'plugins.generic.sliderHome.controllers.grid.SliderHomeGridHandler', 'index') . '">' . __('plugins.generic.sliderHome.tabname') . '</a></li>';
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
	
	// get markup for slider content, incl. containers/wrappers
	function getSliderContent($request) {
		import('plugins.generic.sliderHome.classes.SliderHomeDAO');
		$sliderHomeDao = new SliderHomeDao();
		$contentArray = $sliderHomeDao->getAllContent($request->getContext()->getId());
		$sliderContent = "";
		if (!empty($contentArray)) {
			$sliderContent="<div class='swiper-container'><div class='swiper-wrapper'>";
			foreach ($contentArray as $value) {
				$sliderContent.= "<div class='swiper-slide'>";
				$sliderContent.= $value;
				$sliderContent.= "</div>";
			}
			$sliderContent.= "</div><div class='swiper-pagination'></div></div>";
		}
		return $sliderContent;
	}

	// OJS: thers a template hook on the journal index page
	function callbackIndexJournal($hookName, $args) {		
		$output =& $args[2];
		$request = $this->getRequest();
		$output .= $this->getSliderContent($request);
		$output .= 
			"<script>
				var swiper = new Swiper('.swiper-container', {
					pagination: {
						el: '.swiper-pagination',
						clickable: true,
						renderBullet: function (index, className) {
							return '<span class=\"' + className + '\">' + '</span>';
						},
					},
					speed: 2000,
					autoplay: { delay: 200000,disableOnInteraction:true, stopOnLastSlide:true },
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
					$templateMgr->display($this->getTemplateResource('homeOMP.tpl'));
					return true;					
				}
		}
		return false;
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
