<?php

namespace Wexample\SymfonyBpmn\Api\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyApi\Api\Attribute\ApiResponseData;
use Wexample\SymfonyApi\Api\Attribute\QueryOption\StringQueryOption;
use Wexample\SymfonyApi\Api\Class\ApiResponse;
use Wexample\SymfonyBpmn\Api\Dto\PublicBpmnPathDto;
use Wexample\SymfonyBpmn\Api\Normalizer\Entity\BpmnPath\DefaultBpmnPathNormalizer;
use Wexample\SymfonyHelpers\Controller\AbstractController;

/**
 * The ways through a process, start to end: its scenarios.
 */
#[Route(path: 'api/bpmn-path/', name: 'api_bpmn_path_')]
class BpmnPathController extends AbstractBpmnApiController
{
    #[Route(path: 'list', name: self::ROUTE_LIST, methods: AbstractController::ROUTE_OPTIONS_METHOD_ONLY_GET, options: AbstractController::ROUTE_OPTIONS_ONLY_EXPOSE)]
    #[StringQueryOption(key: self::QUERY_OPTION_PROCESS, required: true)]
    #[ApiResponseData(PublicBpmnPathDto::class, collection: true)]
    public function list(Request $request, DefaultBpmnPathNormalizer $normalizer): ApiResponse
    {
        $processId = $this->resolveProcess($request);

        return self::apiResponseCollection($normalizer->normalizeCollection($this->view->paths($processId)));
    }
}
