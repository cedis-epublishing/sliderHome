<?php
/**
 * @file classes/components/form/SliderContentForm.inc.php
 *
 * Copyright (c) 2021 Universitätsbibliothek Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING..
 * 
 * @brief File implemnting SliderContentForm
 */

use APP\core\Application;
use PKP\components\forms\FieldRichTextarea;
use PKP\components\forms\FieldText;
use PKP\components\forms\FieldOptions;
use PKP\components\forms\FieldUploadImage;
use PKP\components\forms\FormComponent;
use PKP\context\Context;

define('FORM_SLIDER_CONTENT_NEW', 'sliderContent');

/**
 * A form for implementing a slider content dialog.
 * 
 * @class SliderContentForm
 * @brief Class implemnting SliderContentForm
 */
class SliderContentForm_NEW extends FormComponent {
	/** @copydoc FormComponent::$id */
	public $id = FORM_SLIDER_CONTENT_NEW;

	/** @copydoc FormComponent::$method */
	public $method = 'POST';

	public $title;

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
	public function __construct($action, $locales, $context, $baseUrl, $temporaryFileApiUrl, $imageUploadUrl, $publicUrl) {

		$this->action = $action;
		$this->context = $context;
		$this->successMessage = __('plugins.generic.slider.settings.form.success', ['url' => $publicUrl]);
		$this->locales = $this->getLocales();//locales;
		$this->title = 'dasd';

		$this
		->addField(new FieldText('name', [
			'label' => __('plugins.generic.sliderHome.name'),
			'isRequired' => true,
			// 'value' => $data['name'],
			'size' => 'small',
        ]))
		->addField(new FieldRichTextarea('content', [
			'label' => __('plugins.generic.sliderHome.sliderTextContentLabel'),
			'description' => __('plugins.generic.sliderHome.content'),
			'isMultilingual' => true,
			'size' => 'small',
			'toolbar' => 'bold italic superscript subscript | link | blockquote bullist numlist',
			'plugins' => 'paste,link,lists',
		]))
		->addField(new FieldText('copyright', [
			'label' => __('plugins.generic.sliderHome.copyright'),
			'isRequired' => false,
			'isMultilingual' => true,
			// 'value' => $data['copyright'],
			'size' => 'small',
        ]))
		->addField(new FieldOptions('showContent', [
            'label' => __('plugins.generic.sliderHome.showSliderContent'),
            'description' => $description,
            'options' => [
                [
                    'value' => true,
                    'label' => __('plugins.generic.sliderHome.showSliderContent'),
                ],
            ],
            // 'value' => (bool) $data['show_content'],
        ]));
		// image
	}

	/**
     * Get the locales formatted for display in the form
     */
    protected function getLocales(?Context $context = null): array
    {
        $localeNames = $this?->context?->getSupportedFormLocaleNames()
            ?? Application::get()->getRequest()->getSite()->getSupportedLocaleNames();

        return array_map(fn (string $locale, string $name) => ['key' => $locale, 'label' => $name], array_keys($localeNames), $localeNames);
    }
}

?>