<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\JsonResponse;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\Notification\NotificationBag;
use Catalyst\Repository\Settings\Requests\DbConfigRequest;
use Catalyst\Repository\Settings\Support\DbConfigWriter;

final class DbConfigSaveController extends Controller
{
    public function __construct(
        private readonly DbConfigWriter $writer = new DbConfigWriter()
    ) {
        parent::__construct();
    }

    public function saveDb(DbConfigRequest $request): Response
    {
        $probe = $this->writer->save($request->validated());

        if ($probe === 'ok' || $probe === 'db_missing') {
            return $this->jsonSuccessWithToast(null, __('settings.messages.saved'));
        }

        $bag = new NotificationBag();
        $bag->toaster('success', __('settings.messages.saved'))
            ->toaster('warning', __('settings.completion.errors.db_unreachable'));

        return JsonResponse::api(null, true, __('settings.messages.saved'), 200)
            ->withNotification($bag);
    }
}
