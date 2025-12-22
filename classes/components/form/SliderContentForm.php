<?php
/**
 * @file classes/components/form/SliderContentForm.inc.php
 *
 * Copyright (c) 2021 Universitätsbibliothek Freie Universität Berlin
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING..
 * 
 * @brief File implemnting SliderContentForm
 */

namespace APP\plugins\generic\sliderHome\classes\components\form;

use APP\core\Application;
use PKP\components\forms\FieldRichTextarea;
use PKP\components\forms\FieldText;
use PKP\components\forms\FieldOptions;
use PKP\components\forms\FieldUploadImage;
use PKP\components\forms\FormComponent;
use PKP\context\Context;
use PKP\config\Config;

define('FORM_SLIDER_CONTENT', 'sliderContent');

/**
 * A form for implementing a slider content dialog.
 * 
 * @class SliderContentForm
 * @brief Class implemnting SliderContentForm
 */
class SliderContentForm extends FormComponent {
	/** @copydoc FormComponent::$id */
	public $id = FORM_SLIDER_CONTENT;

	/** @copydoc FormComponent::$method */
	public $method = 'POST';

	public string $title;

	public string $searchApiUrl;

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
	public function __construct($action, $context, $baseUrl, $temporaryFileApiUrl, $searchApiUrl) {

		$this->action = $action;
		$this->locales = $this->getLocales($context);
		$this->searchApiUrl = $searchApiUrl;

		$tinyMCEPlugins = Config::getVar('sliderHome', 'tinymceplugins');
		$tinyMCEToolbar = Config::getVar('sliderHome', 'tinymcetoolbar');

		$this
		->addGroup([
            'id' => 'sliderContentGroup',
        ])
		->addField(new FieldText('name', [
			'label' => __('plugins.generic.sliderHome.name'),
			'isRequired' => true,
			'size' => 'medium',
			'groupId' => 'sliderContentGroup',
        ]))
		->addField(new FieldUploadImage('sliderImage', [
			'label' => __('plugins.generic.sliderHome.imageUploadHeading'),
			'baseUrl' => $baseUrl,
			'options' => [
				'url' => $temporaryFileApiUrl,
			],
			'isMultilingual' => true,
			'value' => [],
			'groupId' => 'sliderContentGroup',
		]))
		->addField(new FieldText('sliderImageLink', [
			'label' => __('plugins.generic.sliderHome.sliderImageLink'),
			'size' => 'large',
			'groupId' => 'sliderContentGroup',
		]))
		->addField(new FieldRichTextarea('content', [
			'label' => __('plugins.generic.sliderHome.sliderTextContentLabel'),
			'description' => __('plugins.generic.sliderHome.content'),
			'isMultilingual' => true,
			'size' => 'small',
			'toolbar' => 'bold italic superscript subscript | link | blockquote bullist numlist'.$tinyMCEToolbar,
			'plugins' => explode(',','link,lists'.$tinyMCEPlugins),
			'selector' => 'textarea',
			'readonly' => false,
			'groupId' => 'sliderContentGroup',
		]))
		->addField(new FieldText('copyright', [
			'label' => __('plugins.generic.sliderHome.copyright'),
			'isRequired' => false,
			'isMultilingual' => true,
			'size' => 'small',
			'groupId' => 'sliderContentGroup',
        ]))
		->addField(new FieldOptions('show_content', [
            'label' => __('plugins.generic.sliderHome.showSliderContent'),
            'options' => [
                [
                    'value' => true,
                    'label' => __('plugins.generic.sliderHome.showSliderContent'),
				],
            ],
			'groupId' => 'sliderContentGroup',
        ]));
	}

/**
     * @copydoc ListPanel::getConfig()
     */
    public function getConfig()
    {
        $request = Application::get()->getRequest();
		return parent::getConfig() + [
			'ButtonLabelAdd' => __('plugins.generic.sliderHome.addSliderContent'),
			'ButtonLabelEdit' => __('plugins.generic.sliderHome.editSliderContent'),
			'ButtonLabeladdFromIssue' => __('plugins.generic.sliderHome.addFromIssue'),
			'searchApiUrl' => $this->searchApiUrl,
		];
    }

	/**
     * Get the locales formatted for display in the form
     */
    protected function getLocales(?Context $context = null): array
    {
        $localeNames = $context?->getSupportedFormLocaleNames()
            ?? Application::get()->getRequest()->getSite()->getSupportedLocaleNames();

        return array_map(fn (string $locale, string $name) => ['key' => $locale, 'label' => $name], array_keys($localeNames), $localeNames);
    }
}

?>