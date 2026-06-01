<?php

declare(strict_types=1);

namespace Catalyst\Framework\Api;

final class ApiCatalog
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function routes(): array
    {
        return [
            ['method' => 'GET', 'path' => '/api/v1/catalog', 'permission' => 'manage-api-platform', 'description' => 'Catalogo versionado de la API'],
            ['method' => 'GET', 'path' => '/api/v1/document-templates', 'permission' => 'manage-document-templates', 'description' => 'Listado de plantillas documentales'],
            ['method' => 'GET', 'path' => '/api/v1/document-templates/{id}', 'permission' => 'manage-document-templates', 'description' => 'Detalle de plantilla documental'],
            ['method' => 'POST', 'path' => '/api/v1/document-templates/{id}/preview', 'permission' => 'manage-document-templates', 'description' => 'Previsualización renderizada con datos JSON'],
            ['method' => 'POST', 'path' => '/api/v1/document-templates/{id}/export', 'permission' => 'manage-document-templates', 'description' => 'Exportación real a almacenamiento y artefacto persistido'],
            ['method' => 'GET', 'path' => '/api/v1/automation-rules/{id}', 'permission' => 'manage-automation-rules', 'description' => 'Detalle de una regla interna'],
            ['method' => 'GET', 'path' => '/api/v1/workflows', 'permission' => 'manage-document-templates|manage-automation-rules', 'description' => 'Listado de instancias de flujo'],
            ['method' => 'POST', 'path' => '/api/v1/workflows/{id}/transition', 'permission' => 'manage-document-templates|manage-automation-rules', 'description' => 'Ejecuta una transición de flujo por instancia'],
            ['method' => 'GET', 'path' => '/api/v1/automation-rules', 'permission' => 'manage-automation-rules', 'description' => 'Listado de reglas internas'],
            ['method' => 'POST', 'path' => '/api/v1/automation-rules/{id}/run', 'permission' => 'manage-automation-rules', 'description' => 'Ejecución manual de una regla (requiere Idempotency-Key)'],
            ['method' => 'GET', 'path' => '/api/v1/versions/{resourceKey}/{recordId}', 'permission' => 'manage-document-templates|manage-automation-rules', 'description' => 'Historial de versiones'],
            ['method' => 'POST', 'path' => '/api/v1/versions/{id}/restore', 'permission' => 'manage-document-templates|manage-automation-rules', 'description' => 'Restaura una versión persistida'],
        ];
    }
}
