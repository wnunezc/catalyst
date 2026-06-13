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
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\View\TrustedHtml;

/**
 * Exposes notification-envelope variants for client toaster diagnostics.
 *
 * @package Catalyst\Repository\DevTools\Controllers
 * Responsibility: Returns deterministic toaster, modal and partial-refresh responses.
 */
class ToasterTestController extends Controller
{
    /**
     * Returns a successful response with a success toaster.
     *
     * Responsibility: Returns a successful response with a success toaster.
     */
    public function apiToasterSuccess(): JsonResponse
    {
        return $this->jsonSuccess(['action' => 'created'], __('messages.operation_completed_successfully'))
            ->withNotification(
                $this->toaster('success', __('devtools.toaster_runtime.success.message'), [
                    'title' => __('devtools.toaster_runtime.success.title'),
                    'duration' => 5000,
                ])
            );
    }

    /**
     * Returns an error response with an error toaster.
     *
     * Responsibility: Returns an error response with an error toaster.
     */
    public function apiToasterError(): JsonResponse
    {
        return $this->jsonError(__('devtools.toaster_runtime.error.response'), 400)
            ->withNotification(
                $this->toaster('error', __('devtools.toaster_runtime.error.message'), [
                    'title' => __('devtools.toaster_runtime.error.title'),
                    'duration' => 0,
                ])
            );
    }

    /**
     * Returns a partial-success response with a warning toaster.
     *
     * Responsibility: Returns a partial-success response with a warning toaster.
     */
    public function apiToasterWarning(): JsonResponse
    {
        return $this->jsonSuccess(['status' => 'partial'], __('devtools.toaster_runtime.warning.response'))
            ->withNotification(
                $this->toaster('warning', __('devtools.toaster_runtime.warning.message'), [
                    'title' => __('devtools.toaster_runtime.warning.title'),
                    'duration' => 7000,
                ])
            );
    }

    /**
     * Returns a successful response with an informational toaster.
     *
     * Responsibility: Returns a successful response with an informational toaster.
     */
    public function apiToasterInfo(): JsonResponse
    {
        return $this->jsonSuccess(['info' => 'data'], __('devtools.toaster_runtime.info.response'))
            ->withNotification(
                $this->toaster('info', __('devtools.toaster_runtime.info.message'), [
                    'title' => __('devtools.toaster_runtime.info.title'),
                    'duration' => 5000,
                ])
            );
    }

    /**
     * Returns a response carrying multiple queued toasters.
     *
     * Responsibility: Returns a response carrying multiple queued toasters.
     */
    public function apiMultipleToasters(): JsonResponse
    {
        $notifications = $this->notify()
            ->success(__('devtools.toaster_runtime.multiple.uploaded'), ['title' => __('devtools.toaster_runtime.multiple.upload_title')])
            ->info(__('devtools.toaster_runtime.multiple.processing'), ['title' => __('devtools.toaster_runtime.multiple.processing_title')])
            ->warning(__('devtools.toaster_runtime.multiple.large_file'), ['title' => __('devtools.toaster_runtime.multiple.note_title')]);

        return $this->jsonSuccess(['files' => 3], __('devtools.toaster_runtime.multiple.response'))
            ->withNotification($notifications);
    }

    /**
     * Returns a response that instructs the client to load a modal.
     *
     * Responsibility: Returns a response that instructs the client to load a modal.
     */
    public function apiModalTrigger(): JsonResponse
    {
        return $this->jsonSuccess(['trigger' => 'modal'], __('devtools.toaster_runtime.modal_triggered'))
            ->withNotification(
                $this->modal('/test-features/modal/sample-content', [
                    'title' => __('devtools.toaster_runtime.modal_title'),
                    'size' => 'medium',
                ])
            );
    }

    /**
     * Returns refreshed partial HTML and its update notification.
     *
     * Responsibility: Returns refreshed partial HTML and its update notification.
     */
    public function apiJsEnhancementPartialRefresh(Request $request): JsonResponse
    {
        usleep(900000);

        $probe = (string) $request->get('activity_probe', '');
        if ($probe === 'success') {
            return $this->jsonSuccess(
                ['probe' => 'activity', 'result' => 'success', 'completed_at' => date('H:i:s')],
                __('devtools.activity_runtime.success')
            );
        }

        if ($probe === 'error') {
            return $this->jsonError(__('devtools.activity_runtime.error'), 500);
        }

        $serverTime = date('Y-m-d H:i:s');
        $html = $this->viewEngine->renderPartial('devtools.partials.toaster._js-enhancement-refresh', [
            'server_time' => $serverTime,
        ]);

        return $this->jsonSuccess(['updated_at' => $serverTime], __('devtools.js_runtime.partial_section_refreshed'))
            ->withHtml('#js-enhancements-target', TrustedHtml::fromString($html))
            ->withNotification(
                $this->toaster('success', __('devtools.js_runtime.partial_updated_toast'), [
                    'title' => __('devtools.js_runtime.stage_title'),
                ])
            );
    }
}
