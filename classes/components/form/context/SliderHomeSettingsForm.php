<?php
/**
 * @file classes/components/form/context/SliderHomeSettingsForm.inc.php
 *
 * Copyright (c) 2021 Universitätsbibliothek Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING..
 * 
 * @brief File implemnting SliderHomeSettingsForm
 */

namespace APP\plugins\generic\sliderHome\classes\components\form\context;

use \PKP\components\forms\FormComponent;
use \PKP\components\forms\FieldText;
use \PKP\components\forms\FieldOptions;
use \PKP\components\forms\FieldHTML;

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
	public $method = 'PUT';

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
	public function __construct($action, $locales, $context) {
		$this->action = $action;
		$this->locales = $locales;

		$this->addGroup([
			'id' => 'slidersettings',
		], [])
		->addField(new FieldOptions('slideEffect', [
			'label' => __('plugins.generic.sliderHome.settings.form.slideEffect'),
			'type' => 'radio',
			'options' => [
				['value' => "", 'label' => __('plugins.generic.sliderHome.settings.form.default')],
				['value' => "coverflow", 'label' => __('plugins.generic.sliderHome.settings.form.coverflow')],
				['value' => "cube", 'label' => __('plugins.generic.sliderHome.settings.form.cube')],
			],
			'value' => $context->getData('slideEffect') ?? "",
			'groupId' => 'slidersettings'
		]))
		->addField(new FieldText('maxHeight', [
			'label' => __('plugins.generic.sliderHome.settings.form.maxHeight'),
			'description' => __('plugins.generic.sliderHome.settings.form.maxHeight.description'),
			'isRequired' => false,
			'value' => $context->getData('maxHeight') ?? "100",
			'size' => 'small',
			'groupId' => 'slidersettings',
			'tooltip' => __('plugins.generic.sliderHome.settings.form.groupDescription')
		]))
		->addField(new FieldText('speed', [
			'label' => __('plugins.generic.sliderHome.settings.form.speed'),
			'description' => __('plugins.generic.sliderHome.settings.form.speed.description'),
			'isRequired' => false,
			'value' => $context->getData('speed') ?? "2000",
			'size' => 'small',
			'groupId' => 'slidersettings'
		]))
		->addField(new FieldText('delay', [
			'label' => __('plugins.generic.sliderHome.settings.form.delay'),
			'description' => __('plugins.generic.sliderHome.settings.form.delay.description'),
			'isRequired' => false,
			'value' => $context->getData('delay') ?? "2000",
			'size' => 'small',
			'groupId' => 'slidersettings'
		]))
		->addField(new FieldOptions('stopOnLastSlide', [
			'label' => __('plugins.generic.sliderHome.settings.form.stopOnLastSlide.boxLabel'),
			'options' => [
				['value' => false, 'label' => __('plugins.generic.sliderHome.settings.form.stopOnLastSlide')]
			],
			'value' => (bool) $context->getData('stopOnLastSlide') ?? false,
			'groupId' => 'slidersettings'
		]))
		->addField(new FieldOptions('fallbackLocale', [
			'label' => __('grid.columns.locale'),
			'description' => __('plugins.generic.sliderHome.settings.form.fallbackLocale.description'),
			'type' => 'radio',
			'options' => [
				['value' => "usePrimary", 'label' => __('locale.primary')],
				['value' => "useNone", 'label' => __('plugins.generic.sliderHome.settings.form.fallbackLocale.useNone', ['label' => __('grid.columns.locale')])],
			],
			'value' => $context->getData('fallbackLocale') ?? "usePrimary",
			'groupId' => 'slidersettings'
		]))
		->addGroup([
            'id' => 'descriptionGroup',
			'size' => 'large',
		])
		->addField(new FieldHTML('description', [
			'label' => __('plugins.generic.sliderHome.contentForm.label'),
			'isRequired' => false,
			'description' => __('plugins.generic.sliderHome.contentForm.description'),
			'size' => 'large',
			'groupId' => 'descriptionGroup',
		]));
	}
}