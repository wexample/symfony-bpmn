<?php

namespace Wexample\SymfonyBpmn\Api\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyApi\Api\Attribute\ApiResponseData;
use Wexample\SymfonyApi\Api\Attribute\QueryOption\StringQueryOption;
use Wexample\SymfonyApi\Api\Class\ApiResponse;
use Wexample\SymfonyBpmn\Api\Dto\PublicBpmnLaneDto;
use Wexample\SymfonyBpmn\Api\Normalizer\Entity\BpmnLane\DefaultBpmnLaneNormalizer;
use Wexample\SymfonyHelpers\Controller\AbstractController;

/**
 * The lanes of a process, top first: the bands of its diagram.
 */
#[Route(path: '_api/bpmn-lane/', name: 'api_bpmn_lane_')]
class BpmnLaneController extends AbstractBpmnApiController
{
    #[Route(path: 'list', name: self::ROUTE_LIST, methods: AbstractController::ROUTE_OPTIONS_METHOD_ONLY_GET, options: AbstractController::ROUTE_OPTIONS_ONLY_EXPOSE)]
    #[StringQueryOption(key: self::QUERY_OPTION_PROCESS, required: true)]
    #[ApiResponseData(PublicBpmnLaneDto::class, collection: true)]
    public function list(Request $request, DefaultBpmnLaneNormalizer $normalizer): ApiResponse
    {
        $processId = $this->resolveProcess($request);

        return self::apiResponseCollection($normalizer->normalizeCollection($this->view->lanes($processId)));
    }
}
