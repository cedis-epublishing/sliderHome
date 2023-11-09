<?php
/**
 * @file classes/components/SliderHomeListPanel.php
 * TODO @RS
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SliderHomeListPanel
 *
 * @ingroup classes_components_list
 *
 * @brief A ListPanel component for editing slider image data
 */


use APP\core\Application;
use PKP\components\listPanels\ListPanel;

define('FORM_SLIDER_LIST_PANEL','sliderHomeListPanelComponent');

class SliderHomeListPanel extends ListPanel
{
    /** @copydoc FormComponent::$id */
	public $id = FORM_SLIDER_LIST_PANEL;

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

    /**
     * @copydoc ListPanel::getConfig()
     */
    public function getConfig()
    {
        $request = Application::get()->getRequest();
        $dispatcher = $request->getDispatcher();
        return parent::getConfig() + [
            'addLabel' => __('plugins.generic.sliderHome.addSliderContent'),
            'title' => __('plugins.generic.sliderHome.gridTitle'),
            'apiUrl' => $this->apiUrl,
            'confirmDeleteMessage' => __('plugins.generic.sliderHome.confirmDelete'),
            'count' => $this->count,
            'deleteLabel' => __('plugins.generic.sliderHome.deleteSliderContent'),
            'editLabel' => __('plugins.generic.sliderHome.edit'),
            'form' => $this->form->getConfig(),
            'itemsMax' => $this->itemsMax,
            'urlBase' => $dispatcher->url(
                $request,
                Application::ROUTE_PAGE,
                $request->getContext()->getPath(),
                'announcement',
                'view',
                '__id__'
            ),
            'itemsMax' => count($this->items),
            'getParams' => [
                'contextIds' => [$request->getContext()->getId()],
                'count' => 30,
            ],
        ];
    }
}
