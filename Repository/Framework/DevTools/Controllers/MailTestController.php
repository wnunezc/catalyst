<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework — DevTools
 *
 * MailTestController — Etapa 4: Mail system test.
 *
 * @package   Catalyst\Repository\DevTools\Controllers
 * @author    Walter Nuñez (arcanisgk) <icarosnet@gmail.com>
 * @copyright 2023 - 2025
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace Catalyst\Repository\DevTools\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Mail\MailManager;

class MailTestController extends Controller
{
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
