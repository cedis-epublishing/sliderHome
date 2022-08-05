<?php
/**
 * @file classes/SliderContentListPanel.inc.php
 *
 * Copyright (c) 2021 Universitätsbibliothek Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief A class for loading a panel to show, edit or select slider content
 */

use PKP\components\listPanels\CatalogListPanel;

class SliderContentListPanel extends CatalogListPanel {

	/** @var string URL to the API endpoint where items can be retrieved */
	public $apiUrl = '';

	/** @var integer Number of items to show at one time */
	public $count = 30;

	/** @var array List of user IDs already assigned as a reviewer to this submission */
	public $currentlyAssigned = [];

	/** @var array Query parameters to pass if this list executes GET requests  */
	public $getParams = [];

	/** @var integer Count of total items available for list */
	public $itemsMax = 0;

	/** @var string Name of the input field*/
	public $selectorName = '';

	/** @var array List of user IDs which may not be suitable for anonymous review because of existing access to author details */
	public $warnOnAssignment = [];

	/**
	 * @copydoc ListPanel::set()
	 */
	public function set($args) {
		parent::set($args);
		$this->currentlyAssigned = !empty($args['currentlyAssigned']) ? $args['currentlyAssigned'] : $this->currentlyAssigned;
		$this->warnOnAssignment = !empty($args['warnOnAssignment']) ? $args['warnOnAssignment'] : $this->warnOnAssignment;
	}

	/**
	 * @copydoc ListPanel::getConfig()
	 */
	public function getConfig() {
		$config = parent::getConfig();
		$config['apiUrl'] = $this->apiUrl;
		$config['count'] = $this->count;
		$config['currentlyAssigned'] = $this->currentlyAssigned;
		$config['selectorName'] = $this->selectorName;
		$config['warnOnAssignment'] = $this->warnOnAssignment;
		
		if (!empty($this->getParams)) {
			$config['getParams'] = $this->getParams;
		}

		$config['itemsMax'] = $this->itemsMax;
		$config['canOrder'] = true;

		$config['emptyLabel'] = __('plugins.generic.sliderHome.noneExist');
		
		return $config;
	}

	/**
	 * Helper method to get the items property according to the self::$getParams
	 *
	 * @param Request $request
	 * @return array
	 */
	public function getItems($contextId) {
		import('plugins.generic.sliderHome.classes.SliderHomeDAO');
		$sliderHomeDao = new SliderHomeDAO();
		
		$items = $sliderHomeDao->getByContextId($contextId)->toArray();

		return $items;
	}

	/**
	 * Helper method to get the itemsMax property according to self::$getParams
	 *
	 * @return int
	 */
	public function getItemsMax($contextId) {
		return count($this->getItems($contextId));
	}

	// /**
	//  * Helper method to compile initial params to get items
	//  *
	//  * @return array
	//  */
	// protected function _getItemsParams() {
	// 	return array_merge(
	// 		[
	// 			'offset' => 0,
	// 			'count' => $this->count,
	// 		],
	// 		$this->getParams
	// 	);
	// }
}
