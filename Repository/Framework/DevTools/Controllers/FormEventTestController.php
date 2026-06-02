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
 * Defines the Form Event Test Controller class contract.
 *
 * @package Catalyst\Repository\DevTools\Controllers
 * Responsibility: Coordinates the form event test controller behavior within its module boundary.
 */
class FormEventTestController extends Controller
{
    use HandlesFormEventsTrait;

    /**
     * Handles the form demo store workflow.
     */
    public function formDemoStore(): Response
    {
        return $this->dispatchEvent();
    }

    /**
     * Handles the on save workflow.
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
     * Handles the on validate workflow.
     */
    protected function onValidate(): JsonResponse
    {
        return $this->jsonValidationError([
            'name'  => __('validation.min', ['field' => __('ui.labels.name'), 'min' => 2]),
            'email' => __('devtools.form_events.messages.email_already_registered'),
        ], __('devtools.form_events.messages.forced_validation_errors'));
    }

    /**
     * Handles the on refresh workflow.
     */
    protected function onRefresh(): JsonResponse
    {
        return $this->jsonSuccess(null, __('devtools.form_events.messages.refreshing'))
            ->withNotification($this->toaster('info', __('devtools.form_events.messages.page_refreshing'), ['duration' => 900]))
            ->withRefresh(1000);
    }

    /**
     * Handles the on redirect workflow.
     */
    protected function onRedirect(): JsonResponse
    {
        return $this->jsonSuccess(null, __('messages.redirecting'))
            ->withNotification($this->toaster('success', __('devtools.form_events.messages.redirecting_soon'), ['duration' => 900]))
            ->withRedirect('/test-features', 1000);
    }
}
