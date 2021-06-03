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


import('lib.pkp.classes.form.Form');

class SliderSettingsForm extends Form {

    //
	// Private properties
	//
	/** @var integer */
	var $_contextId;

	/**
	 * Get the context ID.
	 * @return integer
	 */
	function _getContextId() {
		return $this->_contextId;
	}

	/** @var SliderHomePlugin */
	var $_plugin;

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
	function __construct($plugin, $contextId) {
		$this->_plugin = $plugin;
        $this->_contextId = $contextId;
		parent::__construct(method_exists($plugin, 'getTemplateResource') ? $plugin->getTemplateResource('settingsForm.tpl') : $plugin->getTemplatePath() . 'settingsForm.tpl');
		// Add form validation checks.
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
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
		foreach($this->getFormFields() as $settingName => $settingType) {
			$plugin->updateSetting($this->_getContextId(), $settingName, $this->getData($settingName), $settingType);
		}
	}

	/**
	 * @copydoc Form::fetch()
	 */
	function fetch($request = null, $template = null, $display = false) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('maxHeight', $this->getSetting('maxHeight'));
		return parent::fetch($request, $template);
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
