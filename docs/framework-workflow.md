# Catalyst Framework Workflow

`Catalyst\Framework\Workflow` owns reusable lifecycle contracts for framework modules and derived apps. Apps register workflow definitions, persist instances through `WorkflowManager`, and render their own screens over the same transition metadata.

## Contract

Use `WorkflowDefinition` for the declaration:

- `key`: stable workflow key, for example `external-formation.lifecycle`.
- `resourceKey`: generic resource key, for example `external-formation-records`.
- `initialState`: state assigned when a record first enters the workflow.
- `states`: map of state keys to labels.
- `transitions`: list of transition declarations.

Transition declarations support:

- `key`, `label`, `from`, `to`.
- `kind`: semantic UI/API action such as `submit`, `approve`, `reject`, `return`, `resubmit`, `close`, `archive` or `custom`.
- `ability`: resource ability checked through `PermissionRegistry`.
- `guard`: callable that can return `true`, `false` or a denial string.
- `approvals`: dynamic approval requirements read from transition context using dot notation.
- `before` and `after`: optional side effects executed by `WorkflowManager` around persistence.

Definitions can be validated before registration:

```php
$errors = $definition->validate();
```

Validation rejects unknown source/target states, missing transition keys, duplicated transitions and malformed approval requirements.

## Dynamic Approvals

Approvals are declarative. A transition can require one or more approval signals without hard-coding an app table into Catalyst:

```php
[
    'key' => 'approve',
    'label' => 'Approve',
    'from' => ['in_review'],
    'to' => 'approved',
    'kind' => 'approve',
    'ability' => 'approve',
    'approvals' => [
        [
            'key' => 'academic-board',
            'context_key' => 'approvals.academic_board',
            'approved_values' => [true],
        ],
    ],
]
```

The app owns how approvals are stored. Before the transition runs, it passes context such as:

```php
['approvals' => ['academic_board' => true]]
```

## Happy Path

1. App registers a definition with `WorkflowDefinitionRegistry`.
2. Controller or service calls `WorkflowManager::ensureInstance()`.
3. UI lists `WorkflowManager::availableTransitionsForResource()`.
4. Request validation accepts a transition key and optional notes.
5. Service calls `WorkflowManager::transition()`.
6. The manager evaluates state, ability, guard and approvals, then persists the new state and transition audit row.

## Sad Path

The evaluator blocks the transition before persistence when:

- transition is not available from the current state;
- actor lacks the declared ability;
- guard returns `false` or a denial message;
- required approval context is missing or not approved;
- definition validation reports structural errors.

## Smoke

Run:

```powershell
php public/cli.php workflow:smoke --json
```

The smoke uses an in-memory definition and does not require a session, MFA or database connection. It covers dynamic declaration validation, approval required, approval granted, invalid transition, guard blocked and guard allowed.
