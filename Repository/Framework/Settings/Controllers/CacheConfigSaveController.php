<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Response;
use Catalyst\Repository\Settings\Requests\CacheConfigRequest;
use Catalyst\Repository\Settings\Support\CacheConfigWriter;

final class CacheConfigSaveController extends Controller
{
    public function __construct(
        private readonly CacheConfigWriter $writer = new CacheConfigWriter()
    ) {
        parent::__construct();
    }

    public function saveCache(CacheConfigRequest $request): Response
    {
        $this->writer->save($request->validated());

        return $this->jsonSuccessWithToast(null, __('settings.messages.saved'));
    }
}
