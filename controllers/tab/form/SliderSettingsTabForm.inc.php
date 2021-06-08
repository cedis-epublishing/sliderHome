<?php

/**
 * @file plugins/generic/sliderHome/controllers/grid/form/SliderSettingsForm.inc.php
 *
 * Copyright (c) 2017 Center for Digital Systems (CeDiS), Freie Universität Berlin
 * Distributed under the GNU GPL v2. For full terms see the plugin file LICENSE.
 * Author: Ronald Steffen
 * Last update: June 6, 2021
 *
 * @class SliderSettingsForm
 * @ingroup plugins_generic_slider
 *
 * @brief Form for setup SliderHome plugin
 */


import('lib.pkp.classes.controllers.tab.settings.form.ContextSettingsForm');

class SliderSettingsTabForm extends ContextSettingsForm {

    //
	// Private properties
	//
	/** @var integer */
	private $_contextId;

	/**
	 * Set the context ID.
	 * @param context ID integer
	 */
	function _setContextId($contextId) {
		$this->_contextId = $contextId;
	}


	/**
	 * Get the context ID.
	 * @return integer
	 */
	function _getContextId() {
		return $this->_contextId;
	}

	/** @var SliderHomePlugin */
	private $_plugin;


	/**
	 * Set the plugin.
	 * @param plugin
	 */
	function _setPlugin($plugin) {
		$this->_plugin = $plugin;
	}

	/**
	 * Get the plugin.
	 * @return SliderHomePlugin
	 */
	function _getPlugin() {
		return $this->_plugin;
	}

	/**
	 * Constructor
	 * @param $plugin SliderPlugin
	 */
	function __construct($wizardMode = false) {      
		parent::__construct(null, null, $wizardMode);

		// Add form validation checks.
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
		$this->addCheck(new FormValidatorRegExp($this,'maxHeight','required','plugins.generic.slider.settings.form.maxHeight.error','/^([0-9]{1,2}|100)$/i'));
		$this->addCheck(new FormValidatorRegExp($this,'speed','optional','plugins.generic.slider.settings.form.speed.error','/^[0-9]+$/i'));
		$this->addCheck(new FormValidatorRegExp($this,'delay','optional','plugins.generic.slider.settings.form.delay.error','/^[0-9]+$/i'));
	}


	//
	// Implement template methods from Form
	//
	/**
	 * @copydoc Form::initData()
	 */
	function initData() {
		foreach ($this->getFormFields() as $settingName => $settingType) {
			$this->setData($settingName, $this->getSetting($settingName));
		}

		$this->setTemplate($this->_plugin->getTemplateResource('settingsForm.tpl'));

		$this->_contextId = $contextId = Application::getRequest()->getContext()->getId();
		if (!$this->getSetting('speed')) {
			$this->_plugin->updateSetting($contextId, 'speed', 2000, 'int');
		}
		if (!$this->getSetting('delay')) {
			$this->_plugin->updateSetting($contextId, 'delay', 200000, 'int');
		}
		if (!$this->getSetting('stopOnLastSlide')) {
			$this->_plugin->updateSetting($contextId, 'stopOnLastSlide', false, 'bool');
		}
	}

	/**
	 * @copydoc Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array_keys($this->getFormFields()));
	}

	/**
	 * @copydoc Form::execute()
	 */
	function execute(...$functionArgs) {
		$plugin = $this->_getPlugin();

        if ($this->validate()) {
            foreach ($this->getFormFields() as $settingName => $settingType) {
                $plugin->updateSetting($this->_getContextId(), $settingName, $this->getData($settingName), $settingType);
            }
        }
		parent::execute();
	}

	/**
	 * @copydoc Form::fetch()
	 */
	function fetch($request, $template = NULL, $display = false, $params = NULL) {

		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('maxHeight', $this->getSetting('maxHeight'));
		$templateMgr->assign('speed', $this->getSetting('speed'));
		$templateMgr->assign('delay', $this->getSetting('delay'));
		$templateMgr->assign('stopOnLastSlide', $this->getSetting('stopOnLastSlide'));
		$returner = parent::fetch($request, $template, $display);

		return $returner;
	}

	//
	// Public helper methods
	//
	/**
	 * Get a plugin setting.
	 * @param $settingName
	 * @return mixed The setting value.
	 */
	function getSetting($settingName) {
		$plugin = $this->_getPlugin();
		$settingValue = $plugin->getSetting($this->_getContextId(), $settingName);
		return $settingValue;
	}

	/**
	 * Get form fields
	 * @return array (field name => field type)
	 */
	function getFormFields() {
		return array(
			'maxHeight' => 'int',
			'speed' => 'int',
			'delay' => 'int',
			'stopOnLastSlide' => 'bool'
		);
	}

	/**
	 * Is the form field optional
	 * @param $settingName string
	 * @return boolean
	 */
	function isOptional($settingName) {
		return in_array($settingName, array('maxHeight'));
	}
}

?>
