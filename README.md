# symfony-bpmn

Version: 3.0.5

BPMN processes run by Symfony: each compiled into a workflow, its lanes guarding who does what, its automatic tasks, timers and messages handled, its paths and rights listed

## Table of Contents

- [Installation](#installation)

## Installation

```bash
composer require wexample/symfony-bpmn
```

```php
// config/bundles.php
Wexample\SymfonyBpmn\WexampleSymfonyBpmnBundle::class => ['all' => true],
```

Put the processes in `config/bpmn/*.bpmn` — drawn with any BPMN 2.0 tool, or written with php-bpmn's `ProcessBuilder` —, or name other directories:

```yaml
wexample_symfony_bpmn:
    paths: ['%kernel.project_dir%/config/bpmn']
```

`framework.messenger` must be on, and `Wexample\SymfonyBpmn\Message\BpmnTimerMessage` routed to a transport that keeps delayed messages — Doctrine —, consumed by a worker: sent synchronously, a timer fires at once.

`bin/console bpmn:validate` checks every process and compiles it.
