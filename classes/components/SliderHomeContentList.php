<?php
/**
 * @file classes/components/SliderHomeContentList.php
 * TODO @RS
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SliderHomeContentList
 *
 * @ingroup classes_components_list
 *
 * @brief A ListPanel component for editing slider image data
 */

namespace APP\plugins\generic\sliderHome\classes\components;

use APP\core\Application;

define('SLIDER_CONTENT_LIST','sliderHomeContentListComponent');

class SliderHomeContentList
{
    /** @copydoc FormComponent::$id */
	public $id = SLIDER_CONTENT_LIST;

    /** @var string URL to the API endpoint where items can be retrieved */
    public $apiUrl = '';

    /** @var int How many items to display on one page in this list */
    public $count = 30;

    /** @param \PKP\components\forms\announcement\PKPAnnouncementForm Form for adding or editing an email template */
    public $form = null;

    /** @var array Query parameters to pass if this list executes GET requests  */
    public $getParams = [];

    /** @var int Max number of items available to display in this list panel  */
    public $itemsMax = 0;
    public $items = []; //[['id' => 1, 'name' => 'TESTSLIDE A'],['id' => 2, 'name' => 'TESTSLIDE B']];


    public function __construct($apiUrl, $items = [])
    {
        $this->apiUrl = $apiUrl;
        $this->items = $items;
        $this->itemsMax = count($items);
    }

    /**
     * @copydoc ListPanel::getConfig()
     */
    public function getConfig()
    {
        $request = Application::get()->getRequest();
        return [
            'ButtonLabelAdd' => __('plugins.generic.sliderHome.addSliderContent'),
            'ButtonLabelEdit' => __('plugins.generic.sliderHome.editSliderContent'),
            'ButtonLabelShow' => __('plugins.generic.slider.tableview.show'),
            'ButtonLabelHide' => __('plugins.generic.slider.tableview.hide'),
            'ButtonLabeladdFromIssue' => __('plugins.generic.sliderHome.addFromIssue'),
            'apiUrl' => $this->apiUrl,
            'confirmDeleteMessage' => __('plugins.generic.sliderHome.ListPanel.confirmDelete'),
            'count' => $this->count,
            // 'form' => $this->form->getConfig(),
            'items' => $this->items,
            'itemsMax' => count($this->items),
            'getParams' => [
                'contextIds' => [$request->getContext()->getId()],
                'count' => 30,
            ],
            'columns' => [
					['name' => 'sliderName', 'label' => __('plugins.generic.sliderHome.sliderName')],
                    ['name' => 'sliderActions', 'label' => ''],
				]
        ];
    }
}
