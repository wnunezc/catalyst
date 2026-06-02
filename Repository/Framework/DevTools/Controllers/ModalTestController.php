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
use Catalyst\Framework\View\TrustedHtml;

/**
 * Exposes partial HTML and submission responses for modal UI diagnostics.
 *
 * @package Catalyst\Repository\DevTools\Controllers
 * Responsibility: Supplies modal content and validates the modal form harness.
 */
class ModalTestController extends Controller
{
    /**
     * Returns trusted sample HTML for a dynamically loaded modal.
     *
     * Responsibility: Returns trusted sample HTML for a dynamically loaded modal.
     */
    public function modalSampleContent(): Response
    {
        $features = [
            __('devtools.modal_runtime.features.dynamic_loading'),
            __('devtools.modal_runtime.features.html_content'),
            __('devtools.modal_runtime.features.events'),
        ];
        $detailItems = [
            __('devtools.modal_runtime.details.sizes'),
            __('devtools.modal_runtime.details.backdrop'),
            __('devtools.modal_runtime.details.keyboard'),
            __('devtools.modal_runtime.details.scrollable'),
            __('devtools.modal_runtime.details.centered'),
        ];

        return $this->trustedHtmlResponse(TrustedHtml::fromString($this->viewEngine->renderPartial('devtools.partials.modal._sample-content', [
            'features' => $features,
            'detail_items' => $detailItems,
        ])));
    }

    /**
     * Returns trusted HTML for the modal form partial.
     *
     * Responsibility: Returns trusted HTML for the modal form partial.
     */
    public function modalFormContent(): Response
    {
        return $this->trustedHtmlResponse(TrustedHtml::fromString(
            $this->viewEngine->renderPartial('devtools.partials.modal._form-content', [])
        ));
    }

    /**
     * Validates modal form fields and returns the submission result.
     *
     * Responsibility: Validates modal form fields and returns the submission result.
     */
    public function modalFormSubmit(): JsonResponse
    {
        $name  = $this->input('name', '');
        $email = $this->input('email', '');

        if (empty($name) || empty($email)) {
            return $this->jsonError(__('devtools.modal_runtime.errors.required_fields'), 422)
                ->withNotification($this->toaster('error', __('devtools.modal_runtime.errors.fill_required')));
        }

        return $this->jsonSuccess(
            ['name' => $name, 'email' => $email],
            __('devtools.modal_runtime.success.submitted')
        )->withNotification(
            $this->toaster('success', sprintf(__('devtools.modal_runtime.success.contact_toast'), $name, $email), [
                'title' => __('devtools.modal_runtime.success.title'),
                'duration' => 5000,
            ])
        );
    }
}
