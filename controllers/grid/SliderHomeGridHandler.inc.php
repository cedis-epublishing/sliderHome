<?php

/**
 * @file plugins/generic/sliderHome/classes/SliderHomeGridHandler.inc.php
 *
 * Copyright (c) 2021 Universitätsbibliothek Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief File implemeting the slider content form grid handler.
 */

import('lib.pkp.classes.controllers.grid.GridHandler');
import('plugins.generic.sliderHome.controllers.grid.SliderHomeGridRow');
import('plugins.generic.sliderHome.controllers.grid.SliderHomeGridCellProvider');
import('plugins.generic.sliderHome.controllers.grid.form.SliderContentForm');
import('plugins.generic.sliderHome.classes.SliderContent');
import('plugins.generic.sliderHome.classes.SliderHomeDAO');

/**
 * @class SliderHomeGridHandler
 * @brief Class implemeting the slider content form grid handler.
 */
class SliderHomeGridHandler extends GridHandler {

	/** @var StaticPagesPlugin The static pages plugin */
	static $plugin;

	/**
	 * Set the static pages plugin.
	 * @param $plugin StaticPagesPlugin
	 */
	static function setPlugin($plugin) {
		self::$plugin = $plugin;
	}

	/**
	 * Constructor
	 */	
	function __construct() {
		parent::__construct();	
		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER,ROLE_ID_SITE_ADMIN),
			array('index', 'fetchGrid', 'fetchRow','addSliderContent', 'editSliderContent', 'updateSliderContent', 'delete','saveSequence')
		);
	} 

	//
	// Overridden template methods
	//
	/**
	 * @copydoc GridHandler::authorize()
	 */
	function authorize($request, &$args, $roleAssignments) {
		$context = $request->getContext();
		$contextId = $context?$context->getId():CONTEXT_ID_NONE;

		import('lib.pkp.classes.security.authorization.PolicySet');
		$rolePolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);

		import('lib.pkp.classes.security.authorization.RoleBasedHandlerOperationPolicy');
		foreach($roleAssignments as $role => $operations) {
			$rolePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, $role, $operations));
		}
		$this->addPolicy($rolePolicy);
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @copydoc GridHandler::initialize()
	 */
	function initialize($request, $args = null) {
		parent::initialize($request, $args);

		// Basic grid configuration
		$this->setTitle('plugins.generic.sliderHome.gridTitle');

		// Set the no items row text
		$this->setEmptyRowText('plugins.generic.sliderHome.noneExist');

		// Columns
		import('plugins.generic.sliderHome.controllers.grid.SliderHomeGridCellProvider');
		$sliderHomeGridCellProvider = new SliderHomeGridCellProvider();

		$this->addColumn(
			new GridColumn(
				'name',
				'common.title',
				null,
				'controllers/grid/gridCell.tpl',
				$sliderHomeGridCellProvider
			)
		);		

		// Load language components
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_MANAGER);

		// Add grid action.
		$router = $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$this->addAction(
			new LinkAction(
				'addSliderContent',
				new AjaxModal(
					$router->url($request, null, null, 'addSliderContent', null, null),
					__('plugins.generic.sliderHome.addSliderContent'),
					'modal_add_item',
					true
				),
				__('plugins.generic.sliderHome.addSliderContent'),
				'add_item'
			)
		);
	}

	/**
	 * @copydoc GridHandler::loadData()
	 */
	protected function loadData($request, $filter) {
		$context = $request->getContext();

		$contextId = CONTEXT_ID_NONE;
		if ($context) {
			$contextId = $context->getId();
		}

		$sliderHomeDao = new SliderHomeDAO(); 
		return $sliderHomeDao->getByContextId($contextId);
	}

	/**
	 * @copydoc GridHandler::initFeatures()
	 */
	function initFeatures($request, $args) {
		import('lib.pkp.classes.controllers.grid.feature.OrderGridItemsFeature');
		return array(new OrderGridItemsFeature());
	}	
	
	/**
	 * @copydoc GridHandler::getRowInstance()
	 */
	protected function getRowInstance() {
		import('plugins.generic.sliderHome.controllers.grid.SliderHomeGridRow');
		return new SliderHomeGridRow();
	}
	
	//
	// Public Grid Actions
	//
	/**
	 * Display the grid's containing page. 
	 * for OJS 3.1.2
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function index($args, $request) {
		$context = $request->getContext();
		import('lib.pkp.classes.form.Form');
		$form = new Form(self::$plugin->getTemplateResource('websiteSettingsTab.tpl'));

		return new JSONMessage(true, $form->fetch($request));		
	}
	
	/**
	 * An action to add a new user
	 * @param $args array Arguments to the request
	 * @param $request PKPRequest Request object
	 */
	function addSliderContent($args, $request) {
		return $this->editSliderContent($args, $request);
	}

	/**
	 * An action to edit a user
	 * @param $args array Arguments to the request
	 * @param $request PKPRequest Request object
	 * @return string Serialized JSON object
	 */
	function editSliderContent($args, $request) {

		$sliderContentId = (int)$request->getUserVar('sliderContentId');
		$context = $request->getContext();
		$contextId = CONTEXT_ID_NONE;
		if ($context) {
			$contextId = $context->getId();
		}
	
		$sliderContentForm = new SliderContentForm(self::$plugin, $contextId, $sliderContentId);
		$sliderContentForm->initData();

		return new JSONMessage(true, $sliderContentForm->fetch($request));
	}

	/**
	 * Update a user
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateSliderContent($args, $request) {
		$sliderContentId = $request->getUserVar('sliderContentId');		
		$context = $request->getContext();
		$contextId = CONTEXT_ID_NONE;
		if ($context) {
			$contextId = $context->getId();
		}		

		$sliderContentForm = new SliderContentForm(self::$plugin, $contextId, $sliderContentId);
		$sliderContentForm->readInputData();		
		// Check the results
		if ($sliderContentForm->validate()) {			
			// Save the results			
			$sliderContentForm->execute();			
 			return DAO::getDataChangedEvent($sliderContentId);
		} else {		
			return new JSONMessage(false);
		}
	}

	/**                               
	 * @param $args array
	 * Delete a user
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function delete($args, $request) {

		$sliderContentId = $request->getUserVar('sliderContentId');
		$context = $request->getContext();
		$contextId = CONTEXT_ID_NONE;
		if ($context) {
			$contextId = $context->getId();
		}		

		$sliderHomeDao = new SliderHomeDAO();
		$sliderContent = $sliderHomeDao->getById($sliderContentId, $contextId);

		$sliderHomeDao->deleteObject($sliderContent);

		return DAO::getDataChangedEvent();
	}
	/**
	 * @copydoc GridHandler::getDataElementSequence()
	 */
	function getDataElementSequence($gridDataElement) {
		return $gridDataElement->getSequence() ;
	}

	/**
	 * @copydoc GridHandler::setDataElementSequence()
	 */
	function setDataElementSequence($request, $rowId, $gridDataElement, $newSequence) {
		$router = $request->getRouter();
		$context = $router->getContext($request);
		$contextId = CONTEXT_ID_NONE;
		if ($context) {
			$contextId = $context->getId();
		}
		$sliderHomeDao = new SliderHomeDAO();
		$sliderContent = $sliderHomeDao->getById($rowId, $contextId);
		$sliderContent->setSequence($newSequence);
		$sliderHomeDao->updateObject($sliderContent);
	}
}

?>
