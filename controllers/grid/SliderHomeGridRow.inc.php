<?php

/**
 * @file plugins/generic/sliderContent/classes/SliderContentGridRow.inc.php
 *
 * Copyright (c) 2021 Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 * 
 * @brief File implemeting the slider content form grid row.
 */

import('lib.pkp.classes.controllers.grid.GridRow');
import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');

/**
 * @class SliderContentGridRow
 * @brief Class implemeting the slider content form grid row.
 */
class SliderHomeGridRow extends GridRow {

	//
	// Overridden template methods
	//
	/**
	 * @copydoc GridRow::initialize()
	 */
	function initialize($request, $template = null) {
		parent::initialize($request, $template);
		
		
		//$element = $this->getData();
		//assert(is_a($element, 'NavigationMenu'));		

		$sliderContentId = $this->getId();
		if (!empty($sliderContentId) && is_numeric($sliderContentId)) {
			$router = $request->getRouter();

			import('lib.pkp.classes.linkAction.request.AjaxModal');
			$this->addAction(
				new LinkAction(
					'editSliderContent',
					new AjaxModal(
						$router->url($request, null, null, 'editSliderContent', null, array('sliderContentId' => $sliderContentId)),
						__('grid.action.edit'),
						'modal_edit',
						true),
					__('grid.action.edit'),
					null,
					__('plugins.generic.sliderHome.tooltip.editSliderContent')
				)
			);

			import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');
			$this->addAction(
				new LinkAction(
					'delete',
					new RemoteActionConfirmationModal(
						$request->getSession(),
						__('common.confirmDelete'),
						__('grid.action.delete'),
						$router->url($request, null, null, 'delete', null, array('sliderContentId' => $sliderContentId)), 'modal_delete'
					),
					__('grid.action.delete'),
					'delete'
				)
			);
		}
	}
}

?>
