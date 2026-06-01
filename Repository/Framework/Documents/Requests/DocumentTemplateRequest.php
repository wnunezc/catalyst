<?php

declare(strict_types=1);

namespace Catalyst\Repository\Documents\Requests;

use Catalyst\Entities\DocumentTemplate;
use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Document\DocumentTemplateManager;
use Catalyst\Framework\Http\FormRequest;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;

final class DocumentTemplateRequest extends FormRequest
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolvedData = null;

    public function authorize(): bool
    {
        return PermissionRegistry::getInstance()->userHasResourceAbility(
            AuthManager::getInstance()->user(),
            DocumentTemplateManager::RESOURCE_KEY,
            (int) ($this->route('id') ?? 0) > 0 ? 'update' : 'create'
        );
    }

    /**
     * @return string[]
     */
    public function only(): array
    {
        return [
            'name',
            'slug',
            'description',
            'format',
            'variables_schema_json',
            'sample_payload_json',
            'body_template',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|max:150',
            'slug' => 'required|max:150',
            'description' => 'max:1000',
            'format' => 'required|in:html,text,pdf',
            'variables_schema_json' => 'required',
            'sample_payload_json' => 'required',
            'body_template' => 'required',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function labels(): array
    {
        return [
            'name' => __('documents.form_page.labels.template_name'),
            'slug' => __('documents.form_page.labels.template_slug'),
            'description' => __('documents.form_page.labels.description'),
            'format' => __('documents.index.columns.format'),
            'variables_schema_json' => __('documents.form_page.labels.variables_schema_json'),
            'sample_payload_json' => __('documents.form_page.labels.sample_payload_json'),
            'body_template' => __('documents.form_page.labels.body_template'),
        ];
    }

    protected function sensitiveResourceKey(): ?string
    {
        return DocumentTemplateManager::RESOURCE_KEY;
    }

    /**
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
     * @param array<string, mixed> $data
     * @return array<string, string[]>
     */
    private function customErrors(array $data): array
    {
        $errors = [];
        $slug = trim((string) ($data['slug'] ?? ''));
        if ($slug === '') {
            return $errors;
        }

        $currentId = (int) ($this->route('id') ?? 0);
        $existing = DocumentTemplate::query()
            ->whereEqual('slug', $slug)
            ->first();

        if ($existing instanceof DocumentTemplate && (int) $existing->getKey() !== $currentId) {
            $errors['slug'][] = __('documents.validation.template_slug_unique');
        }

        foreach (['variables_schema_json', 'sample_payload_json'] as $field) {
            $decoded = json_decode((string) ($data[$field] ?? ''), true);
            if (!is_array($decoded)) {
                $errors[$field][] = __('documents.validation.valid_json');
            }
        }

        return $errors;
    }
}
