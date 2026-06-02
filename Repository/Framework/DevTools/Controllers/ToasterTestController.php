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
use Catalyst\Framework\View\TrustedHtml;

/**
 * Defines the Toaster Test Controller class contract.
 *
 * @package Catalyst\Repository\DevTools\Controllers
 * Responsibility: Coordinates the toaster test controller behavior within its module boundary.
 */
class ToasterTestController extends Controller
{
    /**
     * Handles the api toaster success workflow.
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
     * Handles the api toaster error workflow.
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
     * Handles the api toaster warning workflow.
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
     * Handles the api toaster info workflow.
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
     * Handles the api multiple toasters workflow.
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
     * Handles the api modal trigger workflow.
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
     * Handles the api js enhancement partial refresh workflow.
     */
    public function apiJsEnhancementPartialRefresh(): JsonResponse
    {
        usleep(900000);

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
