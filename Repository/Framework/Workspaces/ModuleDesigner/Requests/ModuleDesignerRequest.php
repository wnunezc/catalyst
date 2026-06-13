<?php

declare(strict_types=1);

namespace Catalyst\Repository\Workspaces\ModuleDesigner\Requests;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Authorization\PermissionRegistry;
use Catalyst\Framework\Cli\ScaffoldManager;
use Catalyst\Framework\Http\FormRequest;
use Catalyst\Helpers\Exceptions\ForbiddenException;
use Catalyst\Helpers\Exceptions\ValidationException;
use Catalyst\Helpers\Validation\Validator;
use Catalyst\Repository\Workspaces\ModuleDesigner\Support\ModuleDesignerPreviewToken;
use InvalidArgumentException;

/**
 * Authorizes and validates module designer preview and generation payloads.
 */
final class ModuleDesignerRequest extends FormRequest
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolvedData = null;

    public function authorize(): bool
    {
        return PermissionRegistry::getInstance()->userHasResourceAbility(
            AuthManager::getInstance()->user(),
            'workspaces-module-designer',
            'manage'
        );
    }

    /**
     * @return string[]
     */
    public function only(): array
    {
        return [
            'space',
            'module',
            'description',
            'surface',
            'permission_slug',
            'settings',
            'feature_flags',
            'preview_token',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'space' => 'required|in:App,Framework',
            'module' => 'required|max:80',
            'description' => 'max:500',
            'surface' => 'required|in:none,public,workspace,administration,devtools',
            'permission_slug' => 'max:120',
            'settings' => 'max:2000',
            'feature_flags' => 'max:2000',
            'preview_token' => 'max:12000',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function labels(): array
    {
        return [
            'space' => __('workspaces.module_designer.form.labels.space'),
            'module' => __('workspaces.module_designer.form.labels.module'),
            'description' => __('workspaces.module_designer.form.labels.description'),
            'surface' => __('workspaces.module_designer.form.labels.surface'),
            'permission_slug' => __('workspaces.module_designer.form.labels.permission_slug'),
            'settings' => __('workspaces.module_designer.form.labels.settings'),
            'feature_flags' => __('workspaces.module_designer.form.labels.feature_flags'),
            'preview_token' => __('workspaces.module_designer.validation.preview_token'),
        ];
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

    public function validateResolved(): void
    {
        if (!$this->authorize()) {
            throw ForbiddenException::forbidden(__('messages.request_not_authorized'));
        }

        $data = $this->validationData();
        $validator = new Validator($data, $this->rules(), $this->labels());
        $errors = $validator->fails() ? $validator->errors() : [];
        $errors = array_merge_recursive($errors, $this->customErrors($data));

        if ($errors !== []) {
            throw ValidationException::withErrors(
                $errors,
                __('workspaces.module_designer.validation.failed'),
                $this->safeOldInput($data)
            );
        }

        unset($data['preview_token']);
        $this->resolvedData = $data;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string[]>
     */
    private function customErrors(array $data): array
    {
        $errors = [];
        $module = trim((string) ($data['module'] ?? ''));
        $permission = trim((string) ($data['permission_slug'] ?? ''));
        $surface = trim((string) ($data['surface'] ?? ''));

        if ($module !== '' && preg_match('/^[A-Z][A-Za-z0-9]*$/', $module) !== 1) {
            $errors['module'][] = __('workspaces.module_designer.validation.module_name');
        }

        if ($permission !== '' && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $permission) !== 1) {
            $errors['permission_slug'][] = __('workspaces.module_designer.validation.permission_slug');
        }

        if ($permission !== '' && in_array($surface, ['none', 'public'], true)) {
            $errors['permission_slug'][] = __('workspaces.module_designer.validation.permission_surface');
        }

        foreach (['settings', 'feature_flags'] as $field) {
            foreach ($this->listValues((string) ($data[$field] ?? '')) as $value) {
                if (preg_match('/^[a-z][a-z0-9_-]*$/', $value) !== 1) {
                    $errors[$field][] = __('workspaces.module_designer.validation.list_value');
                    break;
                }
            }
        }

        if ($module !== '' && !isset($errors['module'])) {
            try {
                $manager = new ScaffoldManager();
                $space = $manager->normalizeSpace((string) ($data['space'] ?? ''));
                $normalizedModule = $manager->normalizeModuleName($module);
                $baseDir = $manager->moduleBaseDirectory($space, $normalizedModule);
                $allowedRoot = $space === 'Framework'
                    ? implode(DS, [PD, 'Repository', 'Framework'])
                    : implode(DS, [PD, 'Repository', 'App', 'Surface']);

                if (!$this->pathBelongsTo($baseDir, $allowedRoot)) {
                    $errors['module'][] = __('workspaces.module_designer.validation.destination');
                } elseif (is_dir($baseDir)) {
                    $errors['module'][] = __('workspaces.module_designer.validation.destination_exists');
                }
            } catch (InvalidArgumentException) {
                $errors['module'][] = __('workspaces.module_designer.validation.module_name');
            }
        }

        if ($this->isGenerateRequest()) {
            $token = (string) ($data['preview_token'] ?? '');
            $input = $data;
            unset($input['preview_token']);

            if (!(new ModuleDesignerPreviewToken())->verifies($token, $input)) {
                $errors['preview_token'][] = __('workspaces.module_designer.validation.preview_required');
            }
        }

        return $errors;
    }

    /**
     * @return string[]
     */
    private function listValues(string $value): array
    {
        $items = preg_split('/[\r\n,]+/', $value) ?: [];

        return array_values(array_filter(array_map(
            static fn (string $item): string => trim($item),
            $items
        ), static fn (string $item): bool => $item !== ''));
    }

    private function isGenerateRequest(): bool
    {
        return parse_url($this->request()->getUri(), PHP_URL_PATH) === '/workspaces/module-designer/generate';
    }

    private function pathBelongsTo(string $path, string $root): bool
    {
        $normalize = static fn (string $value): string => strtolower(str_replace('\\', '/', rtrim($value, '\\/')));
        $path = $normalize($path);
        $root = $normalize($root);

        return str_starts_with($path . '/', $root . '/');
    }
}
