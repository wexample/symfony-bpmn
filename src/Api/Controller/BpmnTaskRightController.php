<?php

namespace Wexample\SymfonyBpmn\Api\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyApi\Api\Attribute\ApiResponseData;
use Wexample\SymfonyApi\Api\Attribute\QueryOption\LengthQueryOption;
use Wexample\SymfonyApi\Api\Attribute\QueryOption\PageQueryOption;
use Wexample\SymfonyApi\Api\Attribute\QueryOption\SortQueryOption;
use Wexample\SymfonyApi\Api\Attribute\QueryOption\StringQueryOption;
use Wexample\SymfonyApi\Api\Class\ApiResponse;
use Wexample\SymfonyBpmn\Api\Dto\PublicBpmnTaskRightDto;
use Wexample\SymfonyBpmn\Api\Normalizer\Entity\BpmnTaskRight\DefaultBpmnTaskRightNormalizer;
use Wexample\SymfonyHelpers\Controller\AbstractController;

/**
 * Who may do each task of a process: the rights matrix, sortable and paged as
 * a table reads it.
 */
#[Route(path: '_api/bpmn-task-right/', name: 'api_bpmn_task_right_')]
class BpmnTaskRightController extends AbstractBpmnApiController
{
    #[Route(path: 'list', name: self::ROUTE_LIST, methods: AbstractController::ROUTE_OPTIONS_METHOD_ONLY_GET, options: AbstractController::ROUTE_OPTIONS_ONLY_EXPOSE)]
    #[StringQueryOption(key: self::QUERY_OPTION_PROCESS, required: true)]
    #[PageQueryOption]
    #[LengthQueryOption]
    #[SortQueryOption(allowed: ['taskId', 'name', 'laneName', 'role', 'grant'], default: 'laneName')]
    #[ApiResponseData(PublicBpmnTaskRightDto::class, paginated: true)]
    public function list(Request $request, DefaultBpmnTaskRightNormalizer $normalizer): ApiResponse
    {
        $processId = $this->resolveProcess($request);
        [$pagination, $page] = self::applyQueryOptionsToList($request, $this->view->rights($processId));

        return self::apiResponsePaginated($pagination, $normalizer->normalizeCollection($page));
    }
}
