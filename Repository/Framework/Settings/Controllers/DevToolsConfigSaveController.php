<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Response;
use Catalyst\Repository\Settings\Requests\DevToolsConfigRequest;
use Catalyst\Repository\Settings\Support\DevToolsConfigWriter;

final class DevToolsConfigSaveController extends Controller
{
    public function __construct(
        private readonly DevToolsConfigWriter $writer = new DevToolsConfigWriter()
    ) {
        parent::__construct();
    }

    public function saveDevTools(DevToolsConfigRequest $request): Response
    {
        $this->writer->save($request->validated());

        return $this->jsonSuccessWithToast(null, __('settings.messages.saved'));
    }
}
