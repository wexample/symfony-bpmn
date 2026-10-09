<?php

namespace Wexample\SymfonyBpmn\Api\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyApi\Api\Attribute\ApiResponseData;
use Wexample\SymfonyApi\Api\Attribute\QueryOption\StringQueryOption;
use Wexample\SymfonyApi\Api\Class\ApiResponse;
use Wexample\SymfonyBpmn\Api\Dto\PublicBpmnIssueDto;
use Wexample\SymfonyBpmn\Api\Normalizer\Entity\BpmnIssue\DefaultBpmnIssueNormalizer;
use Wexample\SymfonyHelpers\Controller\AbstractController;

/**
 * What the validator says of a process.
 */
#[Route(path: 'api/bpmn-issue/', name: 'api_bpmn_issue_')]
class BpmnIssueController extends AbstractBpmnApiController
{
    #[Route(path: 'list', name: self::ROUTE_LIST, methods: AbstractController::ROUTE_OPTIONS_METHOD_ONLY_GET, options: AbstractController::ROUTE_OPTIONS_ONLY_EXPOSE)]
    #[StringQueryOption(key: self::QUERY_OPTION_PROCESS, required: true)]
    #[ApiResponseData(PublicBpmnIssueDto::class, collection: true)]
    public function list(Request $request, DefaultBpmnIssueNormalizer $normalizer): ApiResponse
    {
        $processId = $this->resolveProcess($request);

        return self::apiResponseCollection($normalizer->normalizeCollection($this->view->issues($processId)));
    }
}
