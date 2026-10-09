<?php

namespace Wexample\SymfonyBpmn\Api\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyApi\Api\Attribute\ApiResponseData;
use Wexample\SymfonyApi\Api\Attribute\QueryOption\StringQueryOption;
use Wexample\SymfonyApi\Api\Class\ApiResponse;
use Wexample\SymfonyBpmn\Api\Dto\PublicBpmnNodeDto;
use Wexample\SymfonyBpmn\Api\Normalizer\Entity\BpmnNode\DefaultBpmnNodeNormalizer;
use Wexample\SymfonyHelpers\Controller\AbstractController;

/**
 * The nodes of a process and where they are drawn.
 */
#[Route(path: 'api/bpmn-node/', name: 'api_bpmn_node_')]
class BpmnNodeController extends AbstractBpmnApiController
{
    #[Route(path: 'list', name: self::ROUTE_LIST, methods: AbstractController::ROUTE_OPTIONS_METHOD_ONLY_GET, options: AbstractController::ROUTE_OPTIONS_ONLY_EXPOSE)]
    #[StringQueryOption(key: self::QUERY_OPTION_PROCESS, required: true)]
    #[ApiResponseData(PublicBpmnNodeDto::class, collection: true)]
    public function list(Request $request, DefaultBpmnNodeNormalizer $normalizer): ApiResponse
    {
        $processId = $this->resolveProcess($request);

        return self::apiResponseCollection($normalizer->normalizeCollection($this->view->nodes($processId)));
    }
}
