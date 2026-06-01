<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework — DevTools
 *
 * FormEventTestController — Etapa 0: HandlesFormEventsTrait + FormHandler demo.
 *
 * @package   Catalyst\Repository\DevTools\Controllers
 * @author    Walter Nuñez (arcanisgk) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Catalyst\Repository\DevTools\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Traits\HandlesFormEventsTrait;

class FormEventTestController extends Controller
{
    use HandlesFormEventsTrait;

    public function formDemoStore(): Response
    {
        return $this->dispatchEvent();
    }

    protected function onSave(): JsonResponse
    {
        $name  = trim((string) $this->input('name', ''));
        $email = trim((string) $this->input('email', ''));

        $errors = [];
        if ($name === '') {
            $errors['name'] = __('validation.required', ['field' => __('ui.labels.name')]);
        }
        if ($email === '') {
            $errors['email'] = __('validation.required', ['field' => __('ui.labels.email')]);
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = __('validation.email', ['field' => __('ui.labels.email')]);
        }

        if ($errors !== []) {
            return $this->jsonValidationError($errors, __('messages.validation_failed'));
        }

        return $this->jsonSuccess(
            ['name' => $name, 'email' => $email],
            __('messages.record_saved_successfully')
        )->withNotification(
            $this->toaster('success', sprintf(__('devtools.form_events.messages.saved_toast'), $name, $email), [
                'title' => __('messages.saved'),
                'duration' => 4000,
            ])
        );
    }

    protected function onValidate(): JsonResponse
    {
        return $this->jsonValidationError([
            'name'  => __('validation.min', ['field' => __('ui.labels.name'), 'min' => 2]),
            'email' => __('devtools.form_events.messages.email_already_registered'),
        ], __('devtools.form_events.messages.forced_validation_errors'));
    }

    protected function onRefresh(): JsonResponse
    {
        return $this->jsonSuccess(null, __('devtools.form_events.messages.refreshing'))
            ->withNotification($this->toaster('info', __('devtools.form_events.messages.page_refreshing'), ['duration' => 900]))
            ->withRefresh(1000);
    }

    protected function onRedirect(): JsonResponse
    {
        return $this->jsonSuccess(null, __('messages.redirecting'))
            ->withNotification($this->toaster('success', __('devtools.form_events.messages.redirecting_soon'), ['duration' => 900]))
            ->withRedirect('/test-features', 1000);
    }
}
