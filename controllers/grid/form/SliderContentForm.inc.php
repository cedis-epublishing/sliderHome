<?php

/**
 * @file plugins/generic/sliderContent/classes/SliderContentForm.inc.php
 *
 * Copyright (c) 2021 Universitätsbibliothek Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
* @brief File implemeting the slider content form.
 */

import('lib.pkp.classes.form.Form');

 /**
 * @class SliderContentForm
 * @brief Form to input slider content.
 */
class SliderContentForm extends Form {

	var $contextId;

	var $sliderContentId;
	
	var $plugin;	

	/**
	 * Constructor
	 */
	function __construct($sliderHomePlugin,$contextId, $sliderContentId = null) {
		$this->contextId = $contextId;
		$this->sliderContentId = $sliderContentId;
		$this->plugin = $sliderHomePlugin;
		
		parent::__construct($sliderHomePlugin->getTemplateResource('sliderContentForm.tpl'));		

		// Add form checks
		$this->addCheck(new FormValidator($this,'name','required', 'plugins.generic.sliderHome.nameRequired'));
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}

	/**
	 * Initialize form data 
	 */
	function initData() {

		if ($this->sliderContentId) {
			$sliderHomeDao = new SliderHomeDAO();
			$sliderContent = $sliderHomeDao->getById($this->sliderContentId, $this->contextId);
			$this->setData('name', $sliderContent->getName());
			$this->setData('content', $sliderContent->getContent());
			$this->setData('showContent', $sliderContent->getShowContent());			
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {	
		$this->readUserVars(array('name','content','showContent'));
	}

	/**
	 * @see Form::fetch
	 */
	function fetch($request, $template = null, $display = false) {

		$templateMgr = TemplateManager::getManager();
		$templateMgr->assign('sliderContentId', $this->sliderContentId);
		
		if (!$this->sliderContentId) {
				$this->setData('content',
"<div>
<p><img src='#'></p>
<div class='slider-text'>
<h3>Title</h3>
<p>Text
<a href='#'>Read more ...</a>
</p>
</div>
</div>");	
		}

		return parent::fetch($request,$template,$display);
	}

	/**
	 * Save form values into the database
	 */
	function execute(...$functionArgs) {
		parent::execute(...$functionArgs);
		$sliderHomeDao = new SliderHomeDAO();
		if ($this->sliderContentId) {
			// Load and update an existing content
			$sliderContent = $sliderHomeDao->getById($this->sliderContentId, $this->contextId);
		} else {
			// Create a new item
			$sliderContent = $sliderHomeDao->newDataObject();
			$sliderContent->setContextId($this->contextId);
		}		
		$sliderContent->setName($this->getData('name'));
		$sliderContent->setContent($this->getData('content'));
		$sliderContent->setShowContent(!empty($this->getData('showContent')));		
		if ($this->sliderContentId) {
			$sliderContent->setSequence($sliderContent->getData('sequence'));
			$sliderHomeDao->updateObject($sliderContent);
		} else {
			$sliderContent->setSequence($sliderHomeDao->getMaxSequence($this->contextId)+1);
			$sliderHomeDao->insertObject($sliderContent);
		}
	}
	
	/**
	 * Perform additional validation checks
	 * @copydoc Form::validate
	 */
	function validate($callHooks = true) {
		return true;
		/*
		$navigationMenuDao = DAORegistry::getDAO('NavigationMenuDAO'); 

		$navigationMenu = $navigationMenuDao->getByTitle($this->_contextId, $this->getData('title'));
		if (isset($navigationMenu) && $navigationMenu->getId() != $this->_navigationMenuId) {
			$this->addError('path', __('manager.navigationMenus.form.duplicateTitle'));
		}

		if ($this->getData('areaName') != '') {
			$navigationMenusWithArea = $navigationMenuDao->getByArea($this->_contextId, $this->getData('areaName'))->toArray();
			if (count($navigationMenusWithArea) == 1 && $navigationMenusWithArea[0]->getId() != $this->_navigationMenuId) {
				$this->addError('areaName', __('manager.navigationMenus.form.menuAssigned'));
			}
		}*/

		return parent::validate(false);
	}	
	
}

?>
