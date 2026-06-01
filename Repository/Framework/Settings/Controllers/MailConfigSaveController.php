<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Response;
use Catalyst\Repository\Settings\Requests\MailConfigRequest;
use Catalyst\Repository\Settings\Support\MailConfigWriter;

final class MailConfigSaveController extends Controller
{
    public function __construct(
        private readonly MailConfigWriter $writer = new MailConfigWriter()
    ) {
        parent::__construct();
    }

    public function saveMail(MailConfigRequest $request): Response
    {
        $this->writer->save($request->validated());

        return $this->jsonSuccessWithToast(null, __('settings.messages.saved'));
    }
}
