<?php

namespace Wexample\SymfonyBpmn\Api\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyApi\Api\Attribute\ApiResponseData;
use Wexample\SymfonyApi\Api\Attribute\QueryOption\LengthQueryOption;
use Wexample\SymfonyApi\Api\Attribute\QueryOption\PageQueryOption;
use Wexample\SymfonyApi\Api\Attribute\QueryOption\SortQueryOption;
use Wexample\SymfonyApi\Api\Class\ApiResponse;
use Wexample\SymfonyBpmn\Api\Dto\PublicBpmnProcessDto;
use Wexample\SymfonyBpmn\Api\Normalizer\Entity\BpmnProcess\DefaultBpmnProcessNormalizer;
use Wexample\SymfonyHelpers\Controller\AbstractController;

/**
 * The processes of the registry, sortable and paged: what a table of them
 * reads.
 */
#[Route(path: 'api/bpmn-process/', name: 'api_bpmn_process_')]
class BpmnProcessController extends AbstractBpmnApiController
{
    #[Route(path: 'list', name: self::ROUTE_LIST, methods: AbstractController::ROUTE_OPTIONS_METHOD_ONLY_GET, options: AbstractController::ROUTE_OPTIONS_ONLY_EXPOSE)]
    #[PageQueryOption]
    #[LengthQueryOption]
    #[SortQueryOption(allowed: ['processId', 'name', 'nodeCount', 'valid'], default: 'processId')]
    #[ApiResponseData(PublicBpmnProcessDto::class, paginated: true)]
    public function list(Request $request, DefaultBpmnProcessNormalizer $normalizer): ApiResponse
    {
        $this->denyUnlessMayView();
        [$pagination, $page] = self::applyQueryOptionsToList($request, $this->view->processes());

        return self::apiResponsePaginated($pagination, $normalizer->normalizeCollection($page));
    }
}
