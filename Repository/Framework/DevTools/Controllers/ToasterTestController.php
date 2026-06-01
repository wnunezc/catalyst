<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework — DevTools
 *
 * ToasterTestController — Etapa 0: Toaster + Modal API endpoints.
 *
 * @package   Catalyst\Repository\DevTools\Controllers
 * @author    Walter Nuñez (arcanisgk) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Catalyst\Repository\DevTools\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\View\TrustedHtml;

class ToasterTestController extends Controller
{
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

    public function apiMultipleToasters(): JsonResponse
    {
        $notifications = $this->notify()
            ->success(__('devtools.toaster_runtime.multiple.uploaded'), ['title' => __('devtools.toaster_runtime.multiple.upload_title')])
            ->info(__('devtools.toaster_runtime.multiple.processing'), ['title' => __('devtools.toaster_runtime.multiple.processing_title')])
            ->warning(__('devtools.toaster_runtime.multiple.large_file'), ['title' => __('devtools.toaster_runtime.multiple.note_title')]);

        return $this->jsonSuccess(['files' => 3], __('devtools.toaster_runtime.multiple.response'))
            ->withNotification($notifications);
    }

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
