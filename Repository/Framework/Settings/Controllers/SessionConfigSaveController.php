<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Response;
use Catalyst\Repository\Settings\Requests\SessionConfigRequest;
use Catalyst\Repository\Settings\Support\SessionConfigWriter;

final class SessionConfigSaveController extends Controller
{
    public function __construct(
        private readonly SessionConfigWriter $writer = new SessionConfigWriter()
    ) {
        parent::__construct();
    }

    public function saveSession(SessionConfigRequest $request): Response
    {
        $this->writer->save($request->validated());

        return $this->jsonSuccessWithToast(null, __('settings.messages.saved'));
    }
}
