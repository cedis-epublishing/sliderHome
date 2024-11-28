<?php

/**
 * @file comtrollers/components/SliderHomeFormHandler.php
 *
 *  TODO @RS
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SliderHomeFormHandlerHandler
 *
 * @ingroup api_v1_announcement
 *
 * @brief Handle API requests for announcement operations.
 *
 */

use APP\core\Application;
use APP\facades\Repo;
use Illuminate\Support\Facades\Bus;
use PKP\db\DAORegistry;
use PKP\facades\Locale;
use PKP\handler\APIHandler;
use PKP\jobs\notifications\NewAnnouncementNotifyUsers;
use PKP\mail\Mailer;
use PKP\notification\NotificationSubscriptionSettingsDAO;
use PKP\notification\PKPNotification;
use PKP\plugins\Hook;
use PKP\security\authorization\PolicySet;
use PKP\security\authorization\RoleBasedHandlerOperationPolicy;
use PKP\security\authorization\UserRolesRequiredPolicy;
use PKP\security\Role;
use PKP\services\PKPSchemaService;

import('plugins.generic.sliderHome.classes.SliderHomeDAO');

class SliderHomeFormHandler extends APIHandler
{
    /** @var int The default number of enties to return in one request */
    public const DEFAULT_COUNT = 30;

    /** @var int The maximum number of entries to return in one request */
    public const MAX_COUNT = 100;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_handlerPath = 'sliderHome';
        parent::__construct();
    }

    // Example APIHandler from the core setup endpoints in the constructor
    // doing this as a plugin results in an infinite loop since parant::__construct
    // also calls 'APIHandler::endpoints'. Also we need to merge our endpoints with
    // the ones provided by 'APIHandler::endpoints' in SliderHomePlugin::callbackSetupEndpoints()
    public function setupEndpoints() {
        $this->_endpoints = [
            // 'GET' => [
            //     [
            //         'pattern' => $this->getEndpointPattern(),
            //         'handler' => [$this, 'getMany'],
            //         'roles' => [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
            //     ],
            //     [
            //         'pattern' => $this->getEndpointPattern() . '/{itemId:\d+}',
            //         'handler' => [$this, 'get'],
            //         'roles' => [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
            //     ],
            // ],
            'POST' => [
                [
                    'pattern' => $this->getEndpointPattern(),
                    'handler' => [$this, 'edit'],
                    'roles' => [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
                ],
            ],
            'PUT' => [
                [
                    'pattern' => $this->getEndpointPattern() . '/{itemId:\d+}',
                    'handler' => [$this, 'edit'],
                    'roles' => [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
                ],
                [
                    'pattern' => $this->getEndpointPattern() . '/toggleVisibility/{itemId:\d+}',
                    'handler' => [$this, 'toggleVisibility'],
                    'roles' => [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
                ],
                [
                    'pattern' => $this->getEndpointPattern() . '/order',
                    'handler' => [$this, 'saveOrder'],
                    'roles' => [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
                ],
            ],
            'DELETE' => [
                [
                    'pattern' => $this->getEndpointPattern() . '/delete/{itemId:\d+}',
                    'handler' => [$this, 'delete'],
                    'roles' => [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
                ],
            ],
        ];
        return $this->_endpoints;
    }

    // As a plugin we need to overwrite this because the API router otherwise searches our handler file in the api folder
    function getEndpointPattern() {
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

    function toggleVisibility($slimRequest, $response, $args) {
        $sliderContentId = $args['itemId'];
        $contextId = $args['contextId'];

        $sliderHomeDao = new SliderHomeDAO();
		$sliderContent = $sliderHomeDao->getById($sliderContentId, $contextId);
		$sliderContent->setShowContent(!$sliderContent->getShowContent());
        $sliderHomeDao->updateObject($sliderContent);
    }

    /**
     * Edit or add new slider content
     *
     * @param \Slim\Http\Request $slimRequest Slim request object
     * @param \PKP\core\APIResponse $response object
     * @param array $args arguments
     *
     * @return \PKP\core\APIResponse
     */
    public function edit($slimRequest, $response, $args)
    {
        $request = $this->getRequest();
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
             $slimRequest->getParsedBody()
        );

        if (!$context) {
            throw new Exception('You can not add a slide without sending a request to the API endpoint of a particular context.');
        }

		$sliderHomeDao = new SliderHomeDAO();
		if (isset($args['itemId'])) {
			// Load and update an existing content
			$sliderContent = $sliderHomeDao->getById($args['itemId'], $context->getId());
		} else {
			// Create a new item
			$sliderContent = $sliderHomeDao->newDataObject();
			$sliderContent->setContextId($context->getId());
		}		
		$sliderContent->setName($data['name']);
		$sliderContent->setContent($data['content']);
		$sliderContent->setShowContent(!empty($data['show_content']));	
		$sliderContent->setCopyright($data['copyright']);

        import('classes.file.PublicFileManager');
		$publicFileManager = new PublicFileManager();
		$baseUrl = $request->getBaseUrl() . '/' . $publicFileManager->getContextFilesPath($context->getId());

		// Copy an uploaded slider file
        foreach ($data['sliderImage'] as $locale => $imageData) {
            if (isset($imageData['temporaryFileId']) && $temporaryFileId = $imageData['temporaryFileId']?:"") {
                $user = $request->getUser();
                $temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO'); /* @var $temporaryFileDao TemporaryFileDAO */
                $temporaryFile = $temporaryFileDao->getTemporaryFile($temporaryFileId, $user->getId());
    
                import('classes.file.PublicFileManager');
                $publicFileManager = new PublicFileManager();
                $newFileName = 'slider_image_' . $locale . '_' . $temporaryFile->getData('fileName') . $publicFileManager->getImageExtension($temporaryFile->getFileType());
                $data['sliderImage'][$locale]['uploadName'] = $newFileName;
                $data['thumbnail'] = true;
				$data['thumbnailUrl'] = $baseUrl.'/'.$newFileName;
                $publicFileManager->copyContextFile($context->getId(), $temporaryFile->getFilePath(), $newFileName);
                $sliderContent->setData('sliderImage', $newFileName, $locale);
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

		if (isset($args['itemId'])) {
			$sliderContent->setSequence($sliderContent->getData('sequence'));
			$sliderHomeDao->updateObject($sliderContent);
		} else {
			$sliderContent->setSequence($sliderHomeDao->getMaxSequence($context->getId())+1);
			$sliderHomeDao->insertObject($sliderContent);
		}

        return $response->withJson(array_merge($data,['id' => $sliderContent->getData('id')]), 200);
    }

    /**
     * Delete a slider entry
     *
     * @param \Slim\Http\Request $slimRequest Slim request object
     * @param \PKP\core\APIResponse $response object
     * @param array $args arguments
     *
     * @return \PKP\core\APIResponse
     */
    public function delete($slimRequest, $response, $args)
    {
        $sliderContentId = $args['itemId'];
        $contextId = $args['contextId'];

        $sliderHomeDao = new SliderHomeDAO();
        $sliderHomeDao->deleteById($sliderContentId);

        return $response->withJson($sliderContentId, 200);
    }

    public function saveOrder($slimRequest, $response, $args) : Response {
        $contextId = (int)$args['contextId'];
        $request = $this->getRequest();
        $context = $request->getContext();

        foreach ($slimRequest->getParsedBody()['sequence'] as $item) {
            $sliderHomeDao = new SliderHomeDAO();
            $sliderContent = $sliderHomeDao->getById($item['id'], $contextId);
            $sliderContent->setSequence($item['sequence']);
            $sliderHomeDao->updateObject($sliderContent);
        }
        return $response->withStatus(200);
    }
}
