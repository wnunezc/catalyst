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
use Catalyst\Framework\Mail\MailManager;

/**
 * Defines the Mail Test Controller class contract.
 *
 * @package Catalyst\Repository\DevTools\Controllers
 * Responsibility: Coordinates the mail test controller behavior within its module boundary.
 */
class MailTestController extends Controller
{
    /**
     * Handles the mail test workflow.
     */
    public function mailTest(): JsonResponse
    {
        $data = $this->only(['to', 'subject', 'body']);

        $rules = [
            'to'      => 'required|email',
            'subject' => 'required|min:1',
            'body'    => 'required|min:1',
        ];

        $validator = $this->validate($data, $rules, [
            'to' => __('devtools.mail_demo.labels.to'),
            'subject' => __('devtools.mail_demo.labels.subject'),
            'body' => __('devtools.mail_demo.labels.body'),
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors(), __('devtools.mail_runtime.invalid_data'));
        }

        try {
            $sent = MailManager::getInstance()
                ->init()
                ->createMessage()
                ->to($data['to'])
                ->subject($data['subject'])
                ->body($data['body'])
                ->send();

            if (!$sent) {
                return $this->jsonError(__('devtools.mail_runtime.send_returned_false'));
            }

            return $this->jsonSuccess(
                ['to' => $data['to'], 'subject' => $data['subject']],
                __('devtools.mail_runtime.sent_successfully')
            )->withNotification(
                $this->toaster('success', sprintf(__('devtools.mail_runtime.sent_to'), $data['to']))
            );
        } catch (\Throwable $e) {
            return $this->jsonError(__('devtools.mail_runtime.error_prefix') . $e->getMessage());
        }
    }
}
