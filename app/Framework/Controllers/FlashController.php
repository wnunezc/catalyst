<?php

declare(strict_types=1);

namespace Catalyst\Framework\Controllers;

use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;

class FlashController extends Controller
{
    public function dismiss(Request $request): Response
    {
        $id = trim((string)$request->input('id', ''));
        if ($id === '') {
            return $this->jsonError('Message ID is required', 400);
        }

        $this->flash()->dismiss($id);

        return $this->jsonSuccess(null, 'Message dismissed');
    }
}
