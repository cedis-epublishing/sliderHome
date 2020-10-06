<?php

/**
 * @file plugins/generic/sliderContent/classes/SliderContent.inc.php
 *
 * Copyright (c) 2016 Language Science Press
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SliderContent
 * Data object representing a slider content.
 */

class SliderContent extends DataObject {

	/**
	 * Constructor
	 */	
	function __construct() {
		parent::__construct();
	} 

	//
	// Get/set methods
	//

	function getContextId(){
		return $this->getData('contextId');
	}

	function setContextId($contextId) {
		return $this->setData('contextId', $contextId);
	}


	function setName($name) {
		return $this->setData('name', $name);
	}

	function getName() {
		return $this->getData('name');
	}


	function setContent($content) {
		return $this->setData('content', $content);
	}

	function getContent() {
		return $this->getData('content');
	}

	function getSequence() {
		return $this->getData('sequence');
	}

	function setSequence($sequence) {
		$this->setData('sequence', $sequence);
	}

}

?>
