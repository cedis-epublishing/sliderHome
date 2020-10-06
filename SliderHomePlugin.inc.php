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
				HookRegistry::register('TemplateManager::display',array($this, 'handleDisplay'));
				HookRegistry::register('Template::Settings::website::appearance', array($this, 'callbackShowWebsiteSettingsTabs'));				
				HookRegistry::register('LoadComponentHandler', array($this, 'setupGridHandler'));				
			}
			return true;
		}
		return false;
	}
	
	/**
	 * Extend the website settings tabs to include static pages
	 * @param $hookName string The name of the invoked hook
	 * @param $args array Hook parameters
	 * @return boolean Hook handling status
	 */
	function callbackShowWebsiteSettingsTabs($hookName, $args) {
		$templateMgr = $args[1];
		$output =& $args[2];
		$request =& Registry::get('request');
		$dispatcher = $request->getDispatcher();

		$output .= $templateMgr->fetch($this->getTemplateResource('sliderTab.tpl'));

		// Permit other plugins to continue interacting with this hook
		return false;
	}

	function handleDisplay($hookName, $args) {
	
		$request = $this->getRequest();
		$templateMgr =& $args[0];
		$template =& $args[1];
			
		switch ($template) {

			case 'frontend/pages/index.tpl':	

				import('plugins.generic.sliderHome.classes.SliderHomeDAO');
				$sliderHomeDao = new SliderHomeDao();
				$contentArray = $sliderHomeDao->getAllContent($request->getPress()->getId());
				
				$sliderContent="<div class='swiper-container'><div class='swiper-wrapper'>";
				foreach ($contentArray as $value) {
					$sliderContent.= "<div class='swiper-slide'>";
					$sliderContent.= $value;
					$sliderContent.= "</div>";
				}
				$sliderContent.= "</div><div class='swiper-pagination'></div></div>";
				$templateMgr->assign('sliderContent',$sliderContent);

				//$templateMgr->assign('title',__('plugins.generic.home.title'));
				//$templateMgr->assign('baseUrl',$request->getBaseUrl());
				//$templateMgr->assign('baseUrl',"langsci-press.org");
									// todo. omp vs ojs
									
				$application = Application::get();
				$applicationName = $application->getName();
				switch ($applicationName) {
					case 'ojs2':
						$templateMgr->display($this->getTemplateResource('homeOJS.tpl'));
						break;
					case 'omp':
						$templateMgr->display($this->getTemplateResource('homeOMP.tpl'));
						break;
					default:
						assert(false);
				}
			return true;
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
		return __('plugins.generic.home.displayName');
	}

	/**
	 * @copydoc PKPPlugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.home.description');
	}
	
	/**
	 * Get the name of the settings file to be installed on new context
	 * creation.
	 * @return string
	 */
	function getContextSpecificPluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}	
}

?>
