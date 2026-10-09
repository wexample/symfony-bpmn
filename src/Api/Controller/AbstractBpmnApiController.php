<?php

namespace Wexample\SymfonyBpmn\Api\Controller;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Wexample\SymfonyApi\Api\Controller\AbstractApiController;
use Wexample\SymfonyBpmn\Service\BpmnViewService;

/**
 * What the lists of a process share: the process asked for (`process`), which
 * has to exist, and the role reading processes takes (`view_role`).
 *
 * Each entity of a process has its endpoint of its own, the `list` of that
 * entity, so that the repository generated from it calls it without being told
 * where it is.
 */
abstract class AbstractBpmnApiController extends AbstractApiController
{
    final public const string ROUTE_LIST = 'list';

    final public const string QUERY_OPTION_PROCESS = 'process';

    public function __construct(
        protected readonly BpmnViewService $view,
        #[Autowire(param: 'wexample_symfony_bpmn.view_role')]
        private readonly string $viewRole,
    ) {
    }

    protected function denyUnlessMayView(): void
    {
        $this->denyAccessUnlessGranted($this->viewRole);
    }

    /**
     * The process asked for, readable and known.
     */
    protected function resolveProcess(Request $request): string
    {
        $this->denyUnlessMayView();
        $processId = (string) self::getQueryOptionValue($request, self::QUERY_OPTION_PROCESS, '');

        if (! $this->view->has($processId)) {
            throw new NotFoundHttpException(sprintf('No process "%s".', $processId));
        }

        return $processId;
    }
}
