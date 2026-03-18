<?php

/**
 * @file comtrollers/components/SliderHomeFormHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SliderHomeFormHandlerHandler
 *
 * @brief Handle API requests for announcement operations.
 *
 */

namespace APP\plugins\generic\sliderHome\controllers\components;

use APP\core\Application;
use PKP\db\DAORegistry;
use PKP\handler\APIHandler;
use APP\file\PublicFileManager;
use PKP\security\authorization\PolicySet;
use PKP\security\authorization\RoleBasedHandlerOperationPolicy;
use PKP\security\authorization\UserRolesRequiredPolicy;
use Exception;
use Illuminate\Http\JsonResponse;
use APP\plugins\generic\sliderHome\classes\SliderHomeDAO;

class SliderHomeFormHandler extends APIHandler
{
    /** @var int The default number of enties to return in one request */
    public const DEFAULT_COUNT = 30;

    /** @var int The maximum number of entries to return in one request */
    public const MAX_COUNT = 100;

    /**
     * Constructor
     */
    public function __construct($controller)
    {
        $this->_handlerPath = 'sliderHome';
        parent::__construct($controller);
    }

    // As a plugin we need to overwrite this because the API router otherwise searches our handler file in the api folder
    function getEndpointPattern(): string {
        return '/{contextPath}/api/{version}/contexts/{contextId}/' . $this->_handlerPath;
    }

    /**
     * @copydoc PKPHandler::authorize
     */
    public function authorize($request, &$args, $roleAssignments)
    {
        $this->addPolicy(new UserRolesRequiredPolicy($request), true);

        $rolePolicy = new PolicySet(PolicySet::COMBINING_PERMIT_OVERRIDES);

        foreach ($roleAssignments as $role => $operations) {
            $rolePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, $role, $operations));
        }
        $this->addPolicy($rolePolicy);

        return parent::authorize($request, $args, $roleAssignments);
    }

    function toggleVisibility($illuminateRequest): JsonResponse {
        $request = Application::get()->getRequest();
        $contextId = $request->getContext()->getId();

        $sliderContentId = (int)$illuminateRequest->route('sliderContentId');

        $sliderHomeDao = new SliderHomeDAO();
		$sliderContent = $sliderHomeDao->getById($sliderContentId, $contextId);
		$sliderContent->setShowContent(!$sliderContent->getShowContent());
        $sliderHomeDao->updateObject($sliderContent);

        return response()->json(['id' => $sliderContentId, 'show_content' => $sliderContent->getShowContent()], 200);
    }

    /**
     * Edit or add new slider content
     *
     * @param IlluminateRequest $illuminateRequest
     * @return JsonResponse
     */
    public function edit($illuminateRequest): JsonResponse
    {
        $request = Application::get()->getRequest();
        $context = $request->getContext();
        $data = array_merge(
            [
                'name' => "",
                'content' => [],
                'show_content' => false,
                'copyright' => [],
                'sliderImage' => [],
                'sliderImageAltText' => "",
                'thumbnail' => false,
                'thumbnailUrl' => ""
            ],
            $request->getUserVars()
        );

        if (!$context) {
            throw new Exception('You can not add a slide without sending a request to the API endpoint of a particular context.');
        }

		$sliderHomeDao = new SliderHomeDAO();
		switch ($data['mode']) {
            case 'edit':
                // Load and update an existing content
                $sliderContent = $sliderHomeDao->getById((int)$request->getUserVar('itemId'), $context->getId());
                break;
            case 'add':
                // Create a new item
                $sliderContent = $sliderHomeDao->newDataObject();
                $sliderContent->setContextId($context->getId());
                break;
            case 'addFromIssue':
                // Create a new item from publication
                $sliderContent = $sliderHomeDao->newDataObject();
                $sliderContent->setContextId($context->getId());
                break;
		}		
		$sliderContent->setName($data['name']);
		$sliderContent->setContent($data['content']);
		$sliderContent->setShowContent(!empty($data['show_content']));	
		$sliderContent->setCopyright($data['copyright']);

		$publicFileManager = new PublicFileManager();
		$baseUrl = $request->getBaseUrl() . '/' . $publicFileManager->getContextFilesPath($context->getId());

		// Copy an uploaded slider file
        foreach ($data['sliderImage'] as $locale => $imageData) {
            switch ($data['mode']) {
                case 'edit':
                case 'add':
                    if (isset($imageData['temporaryFileId']) && $temporaryFileId = $imageData['temporaryFileId']?:"") {
                        // a new file has been uploaded
                        $user = $request->getUser();
                        $temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO'); /* @var $temporaryFileDao TemporaryFileDAO */
                        $temporaryFile = $temporaryFileDao->getTemporaryFile($temporaryFileId, $user->getId());
            
                        $publicFileManager = new PublicFileManager();
                        $newFileName = 'slider_image_' . $locale . '_' . $temporaryFile->getData('fileName') . $publicFileManager->getImageExtension($temporaryFile->getFileType());
                        $data['sliderImage'][$locale]['uploadName'] = $newFileName;
                        $data['thumbnail'] = true;
                        $data['thumbnailUrl'] = $baseUrl.'/'.$newFileName;
                        $publicFileManager->copyContextFile($context->getId(), $temporaryFile->getFilePath(), $newFileName);
                        $sliderContent->setData('sliderImage', $newFileName, $locale);
                    }
                    break;
                case 'addFromIssue':
                    // a cover image may have been selected from an issue, or an existing image is kept
                    if ($data['mode'] == 'addFromIssue' && isset($imageData) && $imageData != "") {
                        // an image from an issue has been selected
                        $sliderContent->setData('sliderImage', $imageData['temporaryFileId'], $locale);
                    }
                    break;
            }

            if ($imageData == "") {
                // we need to delete an existing image
                $filename = $sliderContent->getData('sliderImage')[$locale];
                $publicFileManager->removeContextFile($context->getId(), $filename);
                $sliderContent->setData('sliderImage', NULL, $locale);
            }
            if (isset($imageData['altText'])) {
                $sliderContent->setData('sliderImageAltText', $imageData['altText'], $locale);
            }
        }
        $sliderContent->setSliderImageLink(isset($data['sliderImageLink'])?$data['sliderImageLink']:"");

		switch ($request->getUserVar('mode')) {
            case 'edit':
                // Keep the existing sequence
                $sliderContent->setSequence($sliderContent->getData('sequence'));
                $sliderHomeDao->updateObject($sliderContent);
                break;
            case 'add':
            case 'addFromIssue':
                // Set sequence to the end of the list
                $sliderContent->setSequence($sliderHomeDao->getMaxSequence($context->getId())+1);
			    $sliderHomeDao->insertObject($sliderContent);
                break;
        }

        return response()->json(array_merge($data,['id' => $sliderContent->getData('id')]), 200);
    }

    /**
     * Delete a slider entry
     *
     * @param IlluminateRequest $illuminateRequest
     * @return JsonResponse
     */
    public function delete($illuminateRequest): JsonResponse
    {
        $sliderHomeDao = new SliderHomeDAO();
        $sliderContentId = (int)$illuminateRequest->route('sliderContentId');
        $request = Application::get()->getRequest();
        $contextId = $request->getContext()->getId();

        // Delete associated files for each locale
        $sliderImages = $sliderHomeDao->getById($sliderContentId, $contextId)->getSliderImage();
        $publicFileManager = new PublicFileManager();
        foreach ($sliderImages as $filename) {
            $publicFileManager->removeContextFile($contextId, $filename);
        }
        // Delete database entry
        if ($sliderHomeDao->deleteById($sliderContentId)) {
            return response()->json($sliderContentId, 200);
        } else {
            return response()->json(['error' => 'Could not delete slider content with id '.$sliderContentId], 500);
        }
    }

    public function saveOrder($illuminateRequest) : JsonResponse {
        $request = Application::get()->getRequest();
        $contextId = $request->getContext()->getId();
        $orderedIds = $request->getUserVars()['orderedIds'];

        foreach ($orderedIds as $sequence => $id) {
            $sliderHomeDao = new SliderHomeDAO();
            $sliderContent = $sliderHomeDao->getById($id, $contextId);
            $sliderContent->setSequence($sequence);
            $sliderHomeDao->updateObject($sliderContent);
        }
        return response()->json(200);
    }
}
