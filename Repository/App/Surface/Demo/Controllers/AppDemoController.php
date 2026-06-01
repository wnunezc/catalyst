<?php

declare(strict_types=1);

namespace App\Surface\Demo\Controllers;

use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;

class AppDemoController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('demo.index');
    }
}
