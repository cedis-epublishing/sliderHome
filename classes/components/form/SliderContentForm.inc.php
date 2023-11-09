<?php
/**
 * @file classes/components/form/SliderContentForm.inc.php
 *
 * Copyright (c) 2021 Universitätsbibliothek Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING..
 * 
 * @brief File implemnting SliderContentForm
 */

use \PKP\components\forms\FormComponent;
use \PKP\components\forms\FieldText;
use \PKP\components\forms\FieldOptions;

define('FORM_SLIDER_CONTENT', 'sliderContent');

/**
 * A form for implementing a slider content dialog.
 * 
 * @class SliderContentForm
 * @brief Class implemnting SliderContentForm
 */
class SliderContentForm_NEW extends FormComponent {
	/** @copydoc FormComponent::$id */
	public $id = FORM_SLIDER_CONTENT;

	/** @copydoc FormComponent::$method */
	public $method = 'POST';

	/**
	 * Constructor
	 *
	 * @param string $action string URL to submit the form to
	 * @param array $locales array Supported locales
	 * @param object $context Context Journal or Press to change settings for
	 * @param string $baseUrl string Site's base URL. Used for image previews.
	 * @param string $temporaryFileApiUrl string URL to upload files to
	 * @param string $imageUploadUrl string The API endpoint for images uploaded through the rich text field
	 * @param string $publicUrl url to the frontend page
	 * @param array $data settings for form initialization
	 */
	public function __construct($action, $locales, $context, $baseUrl, $temporaryFileApiUrl, $imageUploadUrl, $publicUrl, $data) {

		$this->action = $action;
		$this->successMessage = __('plugins.generic.slider.settings.form.success', ['url' => $publicUrl]);
		$this->locales = $locales;

		$this->addGroup([
			'id' => 'slidercontent',
			// 'label' => __('plugins.generic.slider.settings.form.groupLabel'),
			// 'description' => __('plugins.generic.slider.settings.form.groupDescription'),
		], [])
		->addField(new FieldText('maxHeight', [
			'label' => __('plugins.generic.slider.settings.form.maxHeight'),
			'description' => __('plugins.generic.slider.settings.form.maxHeight.description'),
			'isRequired' => false,
			'value' => $data['maxHeight'],
			'size' => 'small',
			'groupId' => 'slidersettings',
			'tooltip' => __('plugins.generic.slider.settings.form.groupDescription')
        ]));
	}

}