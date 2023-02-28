<?php
/**
 * @file plugins/generic/sliderContent/classes/SliderContent.inc.php
 *
 * Copyright (c) 2021 Universitätsbibliothek Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 * 
 * @brief File implemeting the slider content data object.
 */

 /**
 * @class SliderContent
 * @brief Data object representing a slider content.
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

	function setCopyright($copyright) {
		return $this->setData('copyright', $copyright);
	}

	function getCopyright() {
		return $this->getData('copyright');
	}

	function getShowContent() {
		return $this->getData('showContent');
	}

	function setShowContent($showContent) {
		$this->setData('showContent', $showContent);
	}	

	function getSequence() {
		return $this->getData('sequence');
	}

	function setSequence($sequence) {
		$this->setData('sequence', $sequence);
	}

	function getSliderImage() {
		return $this->getData('sliderImage');
	}

	function setSliderImage($filename) {
		$this->setData('sliderImage', $filename);
	}
	
	function getSliderImageLink() {
		return $this->getData('sliderImageLink')?:"";
	}

	function setSliderImageLink($link) {
		$this->setData('sliderImageLink', $link);
	}

	function getSliderImageAltText() {
		return $this->getData('sliderImageAltText');
	}

	function setSliderImageAltText($altText) {
		$this->setData('sliderImageAltText', $altText);
	}

	function getLocale() {
		$request = Application::getRequest();
		return $request->getContext()->getPrimaryLocale();
	}
}

?>
