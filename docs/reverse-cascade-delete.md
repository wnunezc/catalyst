# Reverse Cascade Delete

## Purpose

Provide a framework contract for safe deletes that need to inspect dependent records before a root record is removed.

## Contract

`Catalyst\Framework\Deletion\ReverseCascadeDeleteService` builds a preview plan from declared dependencies. The plan must be inspected before execution and requires its confirmation token to run. This keeps destructive workflows out of controllers and prevents derived apps from hard-coding cascade behavior.

Supported actions:

- `archive`
- `detach`
- `delete`
- `soft-delete`

Blocking dependencies use `block_if_present: true`. A plan with blockers is not executable.

## Happy Path

1. The app service gathers dependent records through repositories or DAOs.
2. The app service calls `preview()` with a root resource key, root record id and dependency groups.
3. The UI/API displays the plan, including dependent records and the confirmation token.
4. The app service calls `execute()` only after explicit confirmation.
5. The handler performs repository operations in the order supplied by the plan.

## Sad Path

- If a dependency is marked as blocking and records exist, `isExecutable()` returns `false`.
- If the confirmation token does not match, execution returns a failed result.
- If the handler rejects a planned step, execution stops and reports the already executed steps.
- If an action is outside the allowed action list, `preview()` throws `InvalidArgumentException`.

## Example

```php
$plan = ReverseCascadeDeleteService::getInstance()->preview('documents.template', $templateId, [
    [
        'resource_key' => 'documents.artifacts',
        'action' => 'archive',
        'records' => $artifactRows,
    ],
    [
        'resource_key' => 'workflow.instances',
        'action' => 'archive',
        'block_if_present' => true,
        'records' => $activeWorkflowRows,
    ],
]);
```

Generated documents, audit logs and historical workflow records should normally use `archive`, `detach` or `block_if_present` instead of direct physical deletes.

## Verification

Run:

```powershell
php public/cli.php deletion:smoke --json
```
