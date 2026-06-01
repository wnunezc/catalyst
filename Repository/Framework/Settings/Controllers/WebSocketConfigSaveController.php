<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Response;
use Catalyst\Repository\Settings\Requests\WebSocketConfigRequest;
use Catalyst\Repository\Settings\Support\WebSocketConfigWriter;

final class WebSocketConfigSaveController extends Controller
{
    public function __construct(
        private readonly WebSocketConfigWriter $writer = new WebSocketConfigWriter()
    ) {
        parent::__construct();
    }

    public function saveWebSocket(WebSocketConfigRequest $request): Response
    {
        $this->writer->save($request->validated());

        return $this->jsonSuccessWithToast(null, __('settings.messages.saved'));
    }
}
