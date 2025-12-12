<?php
/**
 * @file plugins/generic/sliderContent/classes/SliderContent.inc.php
 *
 * Copyright (c) 2021 Universitätsbibliothek Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 * 
 * @brief File implemeting the slider content data object.
 */

namespace APP\plugins\generic\sliderHome\classes;

use PKP\core\DataObject;
use APP\core\Application;

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

	function getContextId(): int {
		return $this->getData('contextId');
	}

	function setContextId($contextId): ?int {
		return $this->setData('contextId', $contextId);
	}


	function setName($name): ?string {
		return $this->setData('name', $name);
	}

	function getName(): string {
		return $this->getData('name');
	}

	function setContent($content): ?string {
		return $this->setData('content', $content);
	}

	function getContent(): string {
		return $this->getData('content');
	}

	function setCopyright($copyright): ?string {
		return $this->setData('copyright', $copyright);
	}

	function getCopyright(): string {
		return $this->getData('copyright');
	}

	function getShowContent(): ?bool {
		return (bool)$this->getData('show_content');
	}

	function setShowContent($showContent): ?bool {
		return $this->setData('show_content', (bool)$showContent);
	}	

	function getSequence(): int {
		return $this->getData('sequence');
	}

	function setSequence($sequence): ?int {
		return $this->setData('sequence', $sequence);
	}

	function getSliderImage(): string {
		return $this->getData('sliderImage');
	}

	function setSliderImage($filename): ?string {
		return $this->setData('sliderImage', $filename);
	}
	
	function getSliderImageLink(): string {
		return $this->getData('sliderImageLink')?:"";
	}

	function setSliderImageLink($link): ?string {
		return $this->setData('sliderImageLink', $link);
	}

	function getSliderImageAltText(): string {
		return $this->getData('sliderImageAltText');
	}

	function setSliderImageAltText($altText): ?string {
		return $this->setData('sliderImageAltText', $altText);
	}

	function getLocale(): string {
		$request = Application::getRequest();
		return $request->getContext()->getPrimaryLocale();
	}
}

?>
