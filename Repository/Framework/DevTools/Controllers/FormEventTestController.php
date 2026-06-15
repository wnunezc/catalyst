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

namespace Catalyst\Repository\DevTools\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Traits\HandlesFormEventsTrait;

/**
 * Exercises the form-event response helpers used by interactive forms.
 *
 * @package Catalyst\Repository\DevTools\Controllers
 * Responsibility: Returns deterministic save, validation, refresh and redirect test responses.
 */
class FormEventTestController extends Controller
{
    use HandlesFormEventsTrait;

    /**
     * Dispatches the submitted demo form event to its handler.
     *
     * Responsibility: Dispatches the submitted demo form event to its handler.
     */
    public function formDemoStore(): Response
    {
        return $this->dispatchEvent();
    }

    /**
     * Validates demo contact fields and returns a successful save response.
     *
     * Responsibility: Validates demo contact fields and returns a successful save response.
     */
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

    /**
     * Returns deterministic field-validation errors for the demo harness.
     *
     * Responsibility: Returns deterministic field-validation errors for the demo harness.
     */
    protected function onValidate(): JsonResponse
    {
        return $this->jsonValidationError([
            'name'  => __('validation.min', ['field' => __('ui.labels.name'), 'min' => 2]),
            'email' => __('devtools.form_events.messages.email_already_registered'),
        ], __('devtools.form_events.messages.forced_validation_errors'));
    }

    /**
     * Returns a response that schedules a client refresh.
     *
     * Responsibility: Returns a response that schedules a client refresh.
     */
    protected function onRefresh(): JsonResponse
    {
        return $this->jsonSuccess(null, __('devtools.form_events.messages.refreshing'))
            ->withNotification($this->toaster('info', __('devtools.form_events.messages.page_refreshing'), ['duration' => 900]))
            ->withRefresh();
    }

    /**
     * Returns a response that schedules a client redirect.
     *
     * Responsibility: Returns a response that schedules a client redirect.
     */
    protected function onRedirect(): JsonResponse
    {
        return $this->jsonSuccess(null, __('messages.redirecting'))
            ->withNotification($this->toaster('success', __('devtools.form_events.messages.redirecting_soon'), ['duration' => 900]))
            ->withRedirect('/test-features');
    }
}
