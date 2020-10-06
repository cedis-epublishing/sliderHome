<?php

/**
 * @file plugins/generic/sliderContent/classes/SliderContentGridCellProvider.inc.php
 *
 * Copyright (c) 2016 Language Science Press
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SliderContentGridCellProvider
 *
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class SliderHomeGridCellProvider extends GridCellProvider {

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}
	//
	// Template methods from GridCellProvider
	//

	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn($row, $column) {
		$sliderContent = $row->getData();
		switch ($column->getId()) {
			case 'name':
				// The action has the label
				return array('label' => $sliderContent->getName());
		}
	}
}

?>
