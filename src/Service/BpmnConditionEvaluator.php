<?php

namespace Wexample\SymfonyBpmn\Service;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Throwable;
use Wexample\SymfonyBpmn\Class\BpmnVariables;
use Wexample\SymfonyBpmn\Exception\BpmnRuntimeException;
use Wexample\SymfonyBpmn\Interface\BpmnSubjectInterface;

/**
 * The conditions of a process, written in Symfony's ExpressionLanguage —
 * `variables['quote'] > 200`, `subject.isUrgent()` — over the variables and
 * the subject. The `${…}` other engines wrap their expressions in is
 * dropped.
 */
class BpmnConditionEvaluator
{
    private readonly ExpressionLanguage $expressionLanguage;

    public function __construct(?ExpressionLanguage $expressionLanguage = null)
    {
        $this->expressionLanguage = $expressionLanguage ?? new ExpressionLanguage();
    }

    /**
     * @param list<string> $conditions every one must hold
     * @param list<string> $negated    none may hold
     */
    public function holds(BpmnSubjectInterface $subject, array $conditions, array $negated = []): bool
    {
        foreach ($conditions as $condition) {
            if (! $this->evaluate($subject, $condition)) {
                return false;
            }
        }

        foreach ($negated as $condition) {
            if ($this->evaluate($subject, $condition)) {
                return false;
            }
        }

        return true;
    }

    public function evaluate(BpmnSubjectInterface $subject, string $condition): bool
    {
        return (bool) $this->evaluateValue($subject, $condition);
    }

    /**
     * What an expression gives: a condition's truth, a timer's date.
     */
    public function evaluateValue(BpmnSubjectInterface $subject, string $condition): mixed
    {
        $expression = trim($condition);

        if (preg_match('/^\$\{(.*)\}$/s', $expression, $match)) {
            $expression = trim($match[1]);
        }

        try {
            return $this->expressionLanguage->evaluate($expression, [
                'variables' => new BpmnVariables($subject->getBpmnVariables()),
                'subject' => $subject,
            ]);
        } catch (Throwable $exception) {
            throw new BpmnRuntimeException(sprintf('The condition "%s" cannot be evaluated: %s', $condition, $exception->getMessage()), 0, $exception);
        }
    }
}
