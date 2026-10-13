# symfony-bpmn

Version: 2.0.5

## What it is

A process drawn in BPMN, run by the application: who does each task — its lane —, what the application does on its own, what a timer or a message triggers, which way a choice goes. The drawing is the rule: a client reads it, the application runs it, the tests walk it.

Each process is compiled into a Symfony Workflow (a Petri net) named `bpmn.<process id>`: Symfony's guard, transition and entered events are there under that name, its dumpers too.

## The subject

What a process runs on — a lease, a request, a letter — is an entity of the application:

```php
#[ORM\Entity]
class Lease extends AbstractEntity implements BpmnSubjectInterface
{
    use HasBpmnStateTrait; // the process followed, the places marked, the variables
}
```

`BpmnGroupSubjectInterface` adds `getBpmnGroup()`: the group — a team, an agency — the lanes' grants are asked on.

## Running

```php
$runner->start($request, 'maintenance_request');            // by someone of the start's lane
$runner->complete($request, 'get_quote', ['quote' => 350]);  // a task done, variables its way out reads
$runner->receive($request, 'works_done');                    // a message from outside
$runner->getWaitingNodes($request);                          // where it waits
$runner->getAvailableNodes($request);                        // what the signed-in user may do there
$runner->isFinished($request);
```

After each step the runner does what the application does — service, send, script and business rule tasks, joins — until the process waits for someone, a message or a timer. The subject is changed, never flushed.

- **Choices**: each way out of an exclusive gateway is a transition under its condition, in Symfony's ExpressionLanguage over `variables` and `subject` — `variables['quote'] > 200`, `subject.isUrgent()`; `${…}` is unwrapped —; the default under none of the others. A variable not set reads as null.
- **Parallel**: a split marks every way at once; a join fires once every way in has arrived.
- **Lanes**: a task done by someone is allowed to whoever holds the lane's `role`, or its `grant` on the subject's group (`is_granted(grant, group)` — symfony-user's memberships answer it). They come from the lane (`<wex:property name="role" value="ROLE_AGENT"/>`), or the configuration, which wins:

  ```yaml
  wexample_symfony_bpmn:
      lanes:
          maintenance_request:
              landlord: { role: ROLE_OWNER }
  ```

  A lane naming neither is anyone's. What the application does, a timer, a message, is not asked for a lane.
- **Automatic tasks**: a `BpmnTaskHandlerInterface` does them — `supports($processId, $taskId)`, `handle()` returning variables to set, read by the choice that follows.
- **Timers**: where the process starts to wait at a timer — an intermediate timer event, an interrupting timer boundary event, a timer after an event-based gateway —, `BpmnTimerMessage` is sent with its delay, once per timer event; when it comes, the timer fires if the subject still waits for it, and where it leads to a choice, the conditions decide. A timer may be an expression evaluated then — `${variables['remind_day_before']}`, giving a date or ISO 8601 text —: a reminder the day before a visit, computed by the task that planned it.
- **Messages**: a message event, a receive task, a message boundary event wait for `receive($subject, $messageRef)`. A send task done, a message or signal throw event passed, is told as a `BpmnMessageEvent` — with the `notification` its node names —; every step taken, as a `BpmnTransitionEvent`.

What is run is php-bpmn's executable subset; the rest is drawn, and the validator says so.

## With symfony-activity and symfony-notification

Neither is required; each is followed when installed:

- **symfony-activity**: every step is kept in the history of its subject — category `bpmn`, to declare (`wexample_symfony_activity.categories.bpmn`) —, typed `bpmn.start`, `bpmn.task`, `bpmn.event`, `bpmn.boundary`, `bpmn.join`, with the process, the node, its lane, where it led, and who did it — or `by: application`.
- **symfony-notification**: a message whose node names a `notification` — a category of symfony-notification — is sent to whom the application's `BpmnRecipientResolverInterface` services say (`getRecipients($event)`): the drawing says what is told, the application to whom.

## Rights, paths, scenarios

- `BpmnMatrixService::build($processId)`: each task, its lane, the role or grant, whether the application does it, what interrupts it.
- `BpmnScenarioService::getCoveringPaths($processId)`: a few complete paths taking every flow together, each loop gone round once — php-bpmn's `PathFinder::cover()`, what the paths tab shows —; `getPaths($processId, $maxVisits)`: every combination; `play($subject, $path, $actAs, $variables)` plays one, each step done by `$actAs($lane)` signed in, with the variables given for its node — each timer fired, each message received: a multi-actor test drawn as the process.

## Testing the application against a process

`BpmnScenarioService` tests a process run by this bundle. To test **an application** against a process — each person, at each task, gets the pages they should and only those —, use wexample/symfony-testing's scenarios: a `.bpmn` and a fixtures file side by side, played by HTTP lane by lane, with or without this bundle installed. See symfony-testing's scenarios page.

## Commands

| Command | |
|---|---|
| `bpmn:list` | the processes, their file, whether they are sound |
| `bpmn:validate [process]` | the issues of the validator, then the compilation |
| `bpmn:paths <process> [--all] [--visits=1]` | the covering paths — or every combination —, step by step and lane by lane |
| `bpmn:matrix <process>` | who does each task |
| `bpmn:dump <process>` | the compiled workflow as a Mermaid graph |

## Table of Contents

- [What it is](#what-it-is)
- [The subject](#the-subject)
- [Running](#running)
- [With symfony-activity and symfony-notification](#with-symfony-activity-and-symfony-notification)
- [Rights, paths, scenarios](#rights-paths-scenarios)
- [Testing the application against a process](#testing-the-application-against-a-process)
- [Commands](#commands)
- [Integration in the Suite](#integration-in-the-suite)
- [Dependencies](#dependencies)
- [Versioning & Compatibility Policy](#versioning--compatibility-policy)
- [License](#license)
- [About us](#about-us)
- [Migration Notes](#migration-notes)

## Integration in the Suite

This package is part of the Wexample Suite — a collection of high-quality, modular tools designed to work seamlessly together across multiple languages and environments.

### Related Packages

The suite includes packages for configuration management, file handling, prompts, and more. Each package can be used independently or as part of the integrated suite.

Visit the [Wexample Suite documentation](https://docs.wexample.com) for the complete package ecosystem.

## Dependencies

- php: >=8.5
- wexample/php-bpmn: >=2.0.0
- wexample/symfony-helpers: >=16.0.0
- doctrine/orm: ^3.0
- doctrine/doctrine-bundle: ^2.13 || ^3.0
- symfony/console: ^7.4 || ^8.0
- symfony/event-dispatcher: ^7.4 || ^8.0
- symfony/expression-language: ^7.4 || ^8.0
- symfony/http-kernel: ^7.4 || ^8.0
- symfony/messenger: ^7.4 || ^8.0
- symfony/security-core: ^7.4 || ^8.0
- symfony/workflow: ^7.4 || ^8.0
- wexample/symfony-activity: >=3.0.0
- wexample/symfony-api: >=13.0.0
- wexample/symfony-notification: >=2.0.0
- wexample/php-pseudocode: >=2.2.0
- wexample/symfony-pseudocode: >=3.0.0

## Versioning & Compatibility Policy

Wexample packages follow **Semantic Versioning** (SemVer):

- **MAJOR**: Breaking changes
- **MINOR**: New features, backward compatible
- **PATCH**: Bug fixes, backward compatible

We maintain backward compatibility within major versions and provide clear migration guides for breaking changes.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

Free to use in both personal and commercial projects.

## About us

[Wexample](https://wexample.com) stands as a cornerstone of the digital ecosystem — a collective of seasoned engineers, researchers, and creators driven by a relentless pursuit of technological excellence. More than a media platform, it has grown into a vibrant community where innovation meets craftsmanship, and where every line of code reflects a commitment to clarity, durability, and shared intelligence.

This packages suite embodies this spirit. Trusted by professionals and enthusiasts alike, it delivers a consistent, high-quality foundation for modern development — open, elegant, and battle-tested. Its reputation is built on years of collaboration, refinement, and rigorous attention to detail, making it a natural choice for those who demand both robustness and beauty in their tools.

Wexample cultivates a culture of mastery. Each package, each contribution carries the mark of a community that values precision, ethics, and innovation — a community proud to shape the future of digital craftsmanship.

## Migration Notes

When upgrading between major versions, refer to the migration guides in the documentation.

Breaking changes are clearly documented with upgrade paths and examples.
