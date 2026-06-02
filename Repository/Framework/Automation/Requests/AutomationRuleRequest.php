<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * A modern PHP 8.4 framework for building
 * robust and scalable web applications.
 *
 * PHP Version 8.4 (Required).
 *
 * @package    Catalyst
 *
 * @author     Walter Nuñez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nuñez Cruz and Icaros Net
 * @license    Proprietary - https://catalyst.lh-2.net/license
 *
 * @version    GIT: See repository tags
 *
 * @category   Framework
 * @filesource
 *
 * @link       https://catalyst.lh-2.net Project homepage
 * @see        https://catalyst.lh-2.net/docs Documentation
 *
 */

namespace Catalyst\Repository\Automation\Requests;

use Catalyst\Entities\AutomationRule;
use Catalyst\Entities\DocumentTemplate;
use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Automation\AutomationManager;
use Catalyst\Framework\Http\FormRequest;
use Catalyst\Framework\Temporal\EffectiveWindow;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

/**
 * Validates automation rule create and update payloads.
 *
 * @package Catalyst\Repository\Automation\Requests
 * Responsibility: Authorize automation mutations and enforce rule, JSON, temporal and action constraints.
 */
final class AutomationRuleRequest extends FormRequest
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolvedData = null;

    /**
     * Authorizes creation or update according to the routed automation rule identifier.
     *
     * Responsibility: Authorizes creation or update according to the routed automation rule identifier.
     */
    public function authorize(): bool
    {
        return PermissionRegistry::getInstance()->userHasResourceAbility(
            AuthManager::getInstance()->user(),
            AutomationManager::RESOURCE_KEY,
            (int) ($this->route('id') ?? 0) > 0 ? 'update' : 'create'
        );
    }

    /**
     * Returns the automation rule fields accepted from input.
     *
     * Responsibility: Returns the automation rule fields accepted from input.
     * @return string[]
     */
    public function only(): array
    {
        return [
            'name',
            'slug',
            'description',
            'trigger_type',
            'event_name',
            'cron_expression',
            'condition_json',
            'action_type',
            'action_payload_json',
            'valid_from',
            'valid_to',
        ];
    }

    /**
     * Declares validation rules for automation rule input.
     *
     * Responsibility: Declares validation rules for automation rule input.
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|max:150',
            'slug' => 'required|max:150',
            'description' => 'max:1000',
            'trigger_type' => 'required|in:event,schedule',
            'event_name' => 'max:190',
            'cron_expression' => 'max:100',
            'condition_json' => 'required',
            'action_type' => 'required|in:notification,workflow_transition,render_document',
            'action_payload_json' => 'required',
            'valid_from' => 'max:30',
            'valid_to' => 'max:30',
        ];
    }

    /**
     * Returns translated labels for automation rule validation errors.
     *
     * Responsibility: Returns translated labels for automation rule validation errors.
     * @return array<string, string>
     */
    public function labels(): array
    {
        return [
            'name' => __('automation.form_page.labels.rule_name'),
            'slug' => __('automation.form_page.labels.rule_slug'),
            'description' => __('automation.form_page.labels.description'),
            'trigger_type' => __('automation.form_page.labels.trigger_type'),
            'event_name' => __('automation.form_page.labels.event_name'),
            'cron_expression' => __('automation.form_page.labels.cron_expression'),
            'condition_json' => __('automation.form_page.labels.condition_json'),
            'action_type' => __('automation.form_page.labels.action_type'),
            'action_payload_json' => __('automation.form_page.labels.action_payload_json'),
            'valid_from' => __('automation.form_page.labels.valid_from'),
            'valid_to' => __('automation.form_page.labels.valid_to'),
        ];
    }

    /**
     * Identifies automation rules as the sensitivity policy resource.
     *
     * Responsibility: Identifies automation rules as the sensitivity policy resource.
     */
    protected function sensitiveResourceKey(): ?string
    {
        return AutomationManager::RESOURCE_KEY;
    }

    /**
     * Returns the validated automation rule payload, resolving it lazily.
     *
     * Responsibility: Returns the validated automation rule payload, resolving it lazily.
     * @return array<string, mixed>
     */
    public function validated(): array
    {
        if ($this->resolvedData === null) {
            $this->validateResolved();
        }

        return $this->resolvedData ?? [];
    }

    /**
     * Authorizes and validates the complete automation rule payload.
     *
     * Responsibility: Authorizes and validates the complete automation rule payload.
     * @throws ValidationException
     * @throws ForbiddenException
     */
    public function validateResolved(): void
    {
        if (!$this->authorize()) {
            throw ForbiddenException::forbidden(__('messages.request_not_authorized'));
        }

        $this->prepareForValidation();
        $data = $this->validationData();
        $validator = new Validator($data, $this->rules(), $this->labels());
        $errors = $validator->fails() ? $validator->errors() : [];
        $errors = array_merge_recursive($errors, $this->customErrors($data));

        if ($errors !== []) {
            throw ValidationException::withErrors(
                $errors,
                $this->validationMessage(),
                $this->safeOldInput($data)
            );
        }

        $this->resolvedData = $data;
    }

    /**
     * Validates rule uniqueness, JSON payloads, timing and action-specific requirements.
     *
     * Responsibility: Validates rule uniqueness, JSON payloads, timing and action-specific requirements.
     * @param array<string, mixed> $data
     * @return array<string, string[]>
     */
    private function customErrors(array $data): array
    {
        $errors = [];
        $slug = trim((string) ($data['slug'] ?? ''));
        $currentId = (int) ($this->route('id') ?? 0);

        if ($slug !== '') {
            $existing = AutomationRule::query()
                ->whereEqual('slug', $slug)
                ->first();

            if ($existing instanceof AutomationRule && (int) $existing->getKey() !== $currentId) {
                $errors['slug'][] = __('automation.validation.rule_slug_unique');
            }
        }

        $condition = json_decode((string) ($data['condition_json'] ?? ''), true);
        $actionPayload = json_decode((string) ($data['action_payload_json'] ?? ''), true);

        if (!is_array($condition)) {
            $errors['condition_json'][] = __('automation.validation.valid_json');
        }

        if (!is_array($actionPayload)) {
            $errors['action_payload_json'][] = __('automation.validation.valid_json');

            return $errors;
        }

        $triggerType = (string) ($data['trigger_type'] ?? '');
        if ($triggerType === 'event' && trim((string) ($data['event_name'] ?? '')) === '') {
            $errors['event_name'][] = __('automation.validation.event_name_required');
        }

        if ($triggerType === 'schedule') {
            $expression = trim((string) ($data['cron_expression'] ?? ''));
            $parts = preg_split('/\s+/', $expression) ?: [];
            if (count($parts) !== 5) {
                $errors['cron_expression'][] = __('automation.validation.cron_expression_invalid');
            }
        }

        $actionType = (string) ($data['action_type'] ?? '');
        $validFrom = EffectiveWindow::getInstance()->normalize(isset($data['valid_from']) ? (string) $data['valid_from'] : null);
        $validTo = EffectiveWindow::getInstance()->normalize(isset($data['valid_to']) ? (string) $data['valid_to'] : null);

        if (($data['valid_from'] ?? '') !== '' && $validFrom === null) {
            $errors['valid_from'][] = __('automation.validation.valid_from_invalid');
        }

        if (($data['valid_to'] ?? '') !== '' && $validTo === null) {
            $errors['valid_to'][] = __('automation.validation.valid_to_invalid');
        }

        if ($validFrom !== null && $validTo !== null && strtotime($validTo) <= strtotime($validFrom)) {
            $errors['valid_to'][] = __('automation.validation.valid_to_after_start');
        }

        if ($actionType === 'workflow_transition') {
            foreach (['resource_key', 'record_id_path', 'transition'] as $requiredKey) {
                if (trim((string) ($actionPayload[$requiredKey] ?? '')) === '') {
                    $errors['action_payload_json'][] = __('automation.validation.workflow_transition_requirements');
                    break;
                }
            }
        }

        if ($actionType === 'render_document') {
            $templateId = (int) ($actionPayload['template_id'] ?? 0);
            if ($templateId <= 0 || !(DocumentTemplate::find($templateId) instanceof DocumentTemplate)) {
                $errors['action_payload_json'][] = __('automation.validation.render_document_requires_template');
            }
        }

        if ($actionType === 'notification') {
            if (trim((string) ($actionPayload['title'] ?? '')) === '') {
                $errors['action_payload_json'][] = __('automation.validation.notification_requires_title');
            }
        }

        return $errors;
    }
}
