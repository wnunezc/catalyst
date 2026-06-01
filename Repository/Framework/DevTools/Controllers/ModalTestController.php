<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework — DevTools
 *
 * ModalTestController — Etapa 0: Modal content fragments.
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
use Catalyst\Framework\View\TrustedHtml;

class ModalTestController extends Controller
{
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

    public function modalFormContent(): Response
    {
        return $this->trustedHtmlResponse(TrustedHtml::fromString(
            $this->viewEngine->renderPartial('devtools.partials.modal._form-content', [])
        ));
    }

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
