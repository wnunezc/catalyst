<?php

declare(strict_types=1);

namespace Catalyst\Repository\Api\Controllers;

use Catalyst\Framework\Api\ApiCatalog;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Request;
use Catalyst\Framework\Http\Response;

final class CatalogApiController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeResource('view-any', 'operations-api-management');

        return $this->jsonSuccess([
            'version' => 'v1',
            'authentication' => [
                'type' => __('api.catalog.auth_type'),
                'header' => 'Authorization: Bearer {token}',
                'idempotency_header' => 'Idempotency-Key: {unique-key}',
            ],
            'routes' => ApiCatalog::routes(),
        ], __('api.messages.catalog_retrieved'));
    }
}
