<?php

/**
 * @file plugins/generic/sliderHome/controllers/tab/SliderHomeSettingsTabFormHandler.inc.php
 *fz
 * Copyright (c) 2016 Language Science Press
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SliderHomeGridHandler
 *
 */

import('controllers.tab.settings.WebsiteSettingsTabHandler');
import('plugins.generic.sliderHome.controllers.grid.SliderHomeGridRow');
import('plugins.generic.sliderHome.controllers.grid.SliderHomeGridCellProvider');
import('plugins.generic.sliderHome.controllers.grid.form.SliderHomeForm');
import('plugins.generic.sliderHome.classes.SliderContent');
import('plugins.generic.sliderHome.classes.SliderHomeDAO');

class SliderHomeSettingsTabFormHandler extends WebsiteSettingsTabHandler {

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
		parent::__construct(array(ROLE_ID_MANAGER,ROLE_ID_SITE_ADMIN));

		$this->setPageTabs(array_merge($this->getPageTabs(),
			['sliderHome' => 'plugins.generic.sliderHome.controllers.tab.form.SliderSettingsTabForm']
		));
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
}

?>