<?php
/**
 * @file classes/components/form/context/SliderHomeSettingsForm.inc.php
 *
 * Copyright (c) 2021 Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 * 
 * @brief File implemnting SliderHomeSettingsForm
 */

use \PKP\components\forms\FormComponent;
use \PKP\components\forms\FieldText;
use \PKP\components\forms\FieldOptions;

define('FORM_SLIDER_SETTINGS', 'sliderSettings');

/**
 * A form for implementing slider settings.
 * 
 * @class SliderHomeSettingsForm
 * @brief Class implemnting SliderHomeSettingsForm
 */
class SliderHomeSettingsForm extends FormComponent {
	/** @copydoc FormComponent::$id */
	public $id = FORM_SLIDER_SETTINGS;

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
			'id' => 'slidersettings',
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
		]))
		->addField(new FieldText('speed', [
			'label' => __('plugins.generic.slider.settings.form.speed'),
			'description' => __('plugins.generic.slider.settings.form.speed.description'),
			'isRequired' => false,
			'value' => $data['speed'],
			'size' => 'small',
			'groupId' => 'slidersettings'
		]))
		->addField(new FieldText('delay', [
			'label' => __('plugins.generic.slider.settings.form.delay'),
			'description' => __('plugins.generic.slider.settings.form.delay.description'),
			'isRequired' => false,
			'value' => $data['delay'],
			'size' => 'small',
			'groupId' => 'slidersettings'
		]))
		->addField(new FieldOptions('stopOnLastSlide', [
			'label' => __('plugins.generic.slider.settings.form.stopOnLastSlide.boxLabel'),
			'options' => [
				['value' => false, 'label' => __('plugins.generic.slider.settings.form.stopOnLastSlide')]
			],
			'value' => (bool) $data['stopOnLastSlide'],
			'groupId' => 'slidersettings'
		]));
	}

}