<?php

namespace Wexample\SymfonyBpmn\Api\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyApi\Api\Attribute\ApiResponseData;
use Wexample\SymfonyApi\Api\Attribute\QueryOption\StringQueryOption;
use Wexample\SymfonyApi\Api\Class\ApiResponse;
use Wexample\SymfonyBpmn\Api\Dto\PublicBpmnFlowDto;
use Wexample\SymfonyBpmn\Api\Normalizer\Entity\BpmnFlow\DefaultBpmnFlowNormalizer;
use Wexample\SymfonyHelpers\Controller\AbstractController;

/**
 * The flows of a process and the points their lines go through.
 */
#[Route(path: 'api/bpmn-flow/', name: 'api_bpmn_flow_')]
class BpmnFlowController extends AbstractBpmnApiController
{
    #[Route(path: 'list', name: self::ROUTE_LIST, methods: AbstractController::ROUTE_OPTIONS_METHOD_ONLY_GET, options: AbstractController::ROUTE_OPTIONS_ONLY_EXPOSE)]
    #[StringQueryOption(key: self::QUERY_OPTION_PROCESS, required: true)]
    #[ApiResponseData(PublicBpmnFlowDto::class, collection: true)]
    public function list(Request $request, DefaultBpmnFlowNormalizer $normalizer): ApiResponse
    {
        $processId = $this->resolveProcess($request);

        return self::apiResponseCollection($normalizer->normalizeCollection($this->view->flows($processId)));
    }
}
