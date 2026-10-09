<?php

namespace Wexample\SymfonyBpmn\Exception;

use RuntimeException;

/**
 * A process that cannot be run as asked: unknown, not compilable, a task
 * that is not waited for.
 */
class BpmnRuntimeException extends RuntimeException
{
}
