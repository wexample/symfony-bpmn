<?php

namespace Wexample\SymfonyBpmn\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Wexample\PhpBpmn\Class\Element\Definitions;
use Wexample\PhpBpmn\Class\Element\Process;
use Wexample\PhpBpmn\Class\Validation\BpmnValidator;
use Wexample\PhpBpmn\Class\Validation\ValidationReport;
use Wexample\PhpBpmn\Class\Xml\BpmnXmlReader;
use Wexample\SymfonyBpmn\Class\CompiledProcess;
use Wexample\SymfonyBpmn\Exception\BpmnRuntimeException;
use Wexample\SymfonyHelpers\Attribute\WorkerSafe;

/**
 * The processes of the application: every process of every `.bpmn` file in
 * `paths`, by its id, read once, compiled once.
 */
class BpmnRegistry
{
    /** @var array<string, array{Definitions, Process, string}>|null id => definitions, process, file */
    #[WorkerSafe('The processes the bundled files declare, read once; register() is for tests.')]
    private ?array $processes = null;

    /** @var array<string, CompiledProcess> */
    #[WorkerSafe('Processes compiled from the bundled files, the same for every request.')]
    private array $compiled = [];

    /**
     * @param list<string> $paths
     */
    public function __construct(
        private readonly ProcessCompiler $compiler,
        #[Autowire(param: 'wexample_symfony_bpmn.paths')]
        private readonly array $paths = [],
    ) {
    }

    /**
     * @return array<string, Process>
     */
    public function all(): array
    {
        return array_map(static fn (array $entry) => $entry[1], $this->load());
    }

    public function has(string $processId): bool
    {
        return isset($this->load()[$processId]);
    }

    public function get(string $processId): Process
    {
        return ($this->load()[$processId] ?? throw new BpmnRuntimeException(sprintf('No process "%s" in %s.', $processId, implode(', ', $this->paths))))[1];
    }

    public function getDefinitions(string $processId): Definitions
    {
        $this->get($processId);

        return $this->load()[$processId][0];
    }

    public function getFile(string $processId): string
    {
        $this->get($processId);

        return $this->load()[$processId][2];
    }

    public function compile(string $processId): CompiledProcess
    {
        return $this->compiled[$processId] ??= $this->compiler->compile($this->getDefinitions($processId), $this->get($processId));
    }

    public function validate(string $processId): ValidationReport
    {
        return (new BpmnValidator())->validate($this->getDefinitions($processId));
    }

    /**
     * A process given in code, beside those of the files: a test, a process
     * built at runtime.
     */
    public function register(Definitions $definitions, string $source = 'runtime'): void
    {
        $this->load();

        foreach ($definitions->getProcesses() as $id => $process) {
            $this->processes[$id] = [$definitions, $process, $source];
            unset($this->compiled[$id]);
        }
    }

    /**
     * @return array<string, array{Definitions, Process, string}>
     */
    private function load(): array
    {
        if ($this->processes !== null) {
            return $this->processes;
        }

        $this->processes = [];
        $reader = new BpmnXmlReader();

        foreach ($this->paths as $path) {
            foreach (is_dir($path) ? (glob(rtrim($path, '/') . '/*.bpmn') ?: []) : [] as $file) {
                $definitions = $reader->readFile($file);

                foreach ($definitions->getProcesses() as $id => $process) {
                    if (isset($this->processes[$id])) {
                        throw new BpmnRuntimeException(sprintf('Process "%s" is in both "%s" and "%s".', $id, $this->processes[$id][2], $file));
                    }

                    $this->processes[$id] = [$definitions, $process, $file];
                }
            }
        }

        return $this->processes;
    }
}
