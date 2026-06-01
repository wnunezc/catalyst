# Refactor de DataGrid — Catalyst Framework

## 1. Resumen ejecutivo

El componente `DataGrid` de Catalyst fue refactorizado para reducir el acoplamiento interno de `app/Framework/Admin/Grid/DataGrid.php` sin romper la API pública fluida ni los controladores existentes que ya dependen del grid. El resultado observable en el código actual es una fachada principal que conserva la configuración, resuelve el estado del grid, coordina exportaciones y delega normalización de columnas, filtros, filas, acciones, paginación, URLs y CSV a colaboradores pequeños.

En paralelo, el render compartido de `boot-core/template/components/_admin-datagrid.phtml` dejó de concentrar los controles en el header y pasó a usar toolbars superior e inferior con `Tools`, `per_page`, resumen y paginación completa. El comportamiento interactivo de `print` y `per_page` quedó en `public/assets/js/catalyst/modules/admin-grid.js`, sin `onclick` ni `onchange` inline, alineado con CSP.

## 2. Problema original

Antes del refactor, `DataGrid.php` mezclaba demasiadas responsabilidades en una sola clase: configuración fluida, lectura del `Request`, construcción de URLs, normalización de columnas y filtros, acciones por fila, bulk actions, paginación, preparación de arrays listos para vista, exportación CSV, sanitización para export y helpers de texto.

Ese diseño hacía más costoso modificar una parte específica del grid porque la misma clase concentraba reglas de UI, reglas de estado, reglas de exportación y detalles de infraestructura. En un framework alpha con múltiples grids administrativos, ese acoplamiento elevaba el riesgo de regresión al tocar sorting, export, toolbars o rendering estructurado.

## 3. Objetivo del refactor

El objetivo no fue fragmentar por estilo, sino convertir `DataGrid.php` en el punto de entrada público y dejar las responsabilidades específicas en clases pequeñas y verificables por separado. El refactor buscó:

- preservar la API pública fluida del grid;
- mantener compatibilidad con controladores ya existentes;
- aislar responsabilidades de estado, URLs, filtros, columnas, filas, paginación y export;
- endurecer el render compartido para toolbar, `per_page`, `print` y structured values;
- dejar el componente en una forma suficientemente mantenible para una alpha avanzada camino a RC local.

## 4. Estado anterior vs estado actual

| Aspecto | Estado anterior | Estado actual |
|---|---|---|
| Clase principal | `DataGrid.php` concentraba gran parte de la lógica | `DataGrid.php` actúa como fachada/orquestador predominante |
| Estado del grid | Resolución embebida en la clase principal | `DataGridStateResolver` resuelve `page`, `per_page`, `sort`, `direction`, `search`, `filters`, `query` |
| URLs | Construcción distribuida dentro del grid | `DataGridUrlBuilder` centraliza merge y build de query params |
| Texto auxiliar | Helpers mezclados con la lógica del grid | `DataGridTextFormatter` aísla `humanize()` y `slugify()` |
| Columnas | Normalización y sort mezclados en la clase principal | `DataGridColumnNormalizer` prepara columnas y URLs de orden |
| Filtros | Normalización mezclada con lectura de request | `DataGridFilterNormalizer` trabaja sobre estado ya resuelto |
| Filas | Resolución de celdas, structured values y export en la clase principal | `DataGridRowNormalizer` centraliza celdas, structured values y sanitización de export |
| Acciones por fila | Resolución e interpolación embebidas | `DataGridRowActionNormalizer` normaliza acciones e interpolaciones |
| Bulk actions | Preparación en la clase principal | `DataGridBulkActionNormalizer` normaliza acciones masivas y preserva query útil |
| Paginación | Metadata y navegación dentro del grid | `DataGridPaginationBuilder` construye metadata y URLs incluyendo `First` y `Last` |
| Export CSV | Lógica mezclada con el grid | `DataGridCsvExporter` genera el stream CSV físico |
| Export tools | Botones y formatos acoplados | `DataGridExportNormalizer` prepara opciones de export y `print`; el dropdown final se arma en scope/template |
| Template | Header más cargado | Header solo de identidad; toolbars arriba y abajo |
| JS | Riesgo de handlers inline | `admin-grid.js` activa `print`, `per_page` y bulk behavior desde JS externo |

## 5. Nueva estructura de archivos

Árbol actual confirmado en `app/Framework/Admin/Grid/`:

```text
app/Framework/Admin/Grid/
├── DataGrid.php
├── DataGridBulkActionNormalizer.php
├── DataGridColumnNormalizer.php
├── DataGridCsvExporter.php
├── DataGridExportNormalizer.php
├── DataGridFilterNormalizer.php
├── DataGridPaginationBuilder.php
├── DataGridRowActionNormalizer.php
├── DataGridRowNormalizer.php
├── DataGridStateResolver.php
├── DataGridTextFormatter.php
└── DataGridUrlBuilder.php
```

Superficies relacionadas en render/runtime:

- `boot-core/template/components/_admin-datagrid.phtml`
- `boot-core/template/components/_admin-datagrid-cell.phtml`
- `boot-core/template/scope/components/_admin-datagrid.php`
- `public/assets/js/catalyst/modules/admin-grid.js`

## 6. Responsabilidad de cada clase

### 6.1 `DataGrid.php`

**Rol**

Fachada/orquestador principal del componente.

**Responsabilidades confirmadas por código**

- Mantener la API pública fluida del grid.
- Recibir configuración general, columnas, filtros, acciones, bulk actions y provider.
- Resolver el grid completo para vista mediante `resolve(Request $request): array`.
- Delegar a colaboradores para estado, columnas, filas, filtros, paginación, export tools y bulk actions.
- Exponer exportación genérica mediante `export(Request $request): Response`.
- Mantener compatibilidad hacia atrás con `exportCsv(Request $request): Response`.
- Exponer helpers estáticos para valores visuales estructurados:
  - `stack()`
  - `code()`
  - `badge()`
  - `badges()`
  - `booleanBadge()`

**Métodos públicos de configuración confirmados**

- `baseUrl()`
- `title()`
- `emptyState()`
- `columns()`
- `filters()`
- `actions()`
- `bulkActions()`
- `exportFormats()`
- `resourceKey()`
- `rowKey()`
- `defaultSort()`
- `pagination()`
- `searchPlaceholder()`
- `provider()`
- `printEnabled()`

**Notas técnicas**

- `DataGrid.php` ya no concentra toda la lógica pesada, pero todavía conserva utilidades privadas de orquestación, respuesta y normalización interna de formatos para `exportFormat()`.
- Sigue siendo el punto de entrada público del componente y no debe sustituirse por los colaboradores internos en controladores.
- `rowKey()` sigue siendo API pública activa. Aunque un IDE marque pocos usos directos internos, el método permite definir identificadores de fila distintos de `id`, por ejemplo `uuid`, `slug`, `permission_id`, `code` o `external_id`.
- `exportCsv()` hoy existe como wrapper real hacia `export()`.

**Pendiente de verificación**

- El prompt propone marcar `exportCsv()` como `@deprecated`, pero el código actual solo mantiene el wrapper; la anotación `@deprecated` no está implementada en la clase revisada.

### 6.2 `DataGridUrlBuilder.php`

**Rol**

Construcción consistente de URLs con query params.

**Responsabilidades confirmadas**

- `mergeQuery(array $query, array $overrides): array`
- `build(string $baseUrl, array $query = []): string`
- Eliminar parámetros cuando el override llega en `null` o `''`.
- Preservar filtros, búsqueda, sort y `per_page` cuando otro colaborador vuelve a componer navegación o export.

**Uso actual**

- Paginación.
- Sort URLs.
- Export links.
- Bulk action links.

### 6.3 `DataGridTextFormatter.php`

**Rol**

Formateo simple de texto.

**Responsabilidades confirmadas**

- `humanize(string $value): string`
- `slugify(?string $value, string $fallback = 'grid-export'): string`

**Uso actual**

- Labels automáticos de columnas.
- Labels automáticos de filtros.
- Nombre de archivo para exportación.

### 6.4 `DataGridCsvExporter.php`

**Rol**

Generación física del contenido CSV.

**Responsabilidades confirmadas**

- Abrir `php://temp`.
- Escribir encabezados con `fputcsv`.
- Escribir filas con `fputcsv`.
- Rebobinar el stream y devolver el contenido como `string`.

**Límite de responsabilidad**

- No decide qué columnas se exportan.
- No aplica `SensitiveDataPolicy`.
- No sanitiza el contenido semántico de cada celda.

### 6.5 `DataGridStateResolver.php`

**Rol**

Resolver el estado del grid a partir del `Request` y la configuración del grid.

**Responsabilidades confirmadas**

- Resolver `page`.
- Resolver `per_page`.
- Resolver `sort`.
- Resolver `direction`.
- Resolver `search`.
- Resolver `filters`.
- Exponer `query` desde `getAllGet()`.

**Comportamiento confirmado**

- Valida que el `sort` solicitado pertenezca a columnas marcadas como `sortable`.
- Valida que `direction` sea `asc` o `desc`.
- Valida `per_page` contra `per_page_options`.
- Si `per_page` no es válido, vuelve al default efectivo del grid.

### 6.6 `DataGridFilterNormalizer.php`

**Rol**

Preparar filtros para render.

**Responsabilidades confirmadas**

- Normalizar `name`, `label`, `type`, `value`, `placeholder`, `options`, `attributes`.
- Resolver labels automáticos con `DataGridTextFormatter`.
- Consumir `state` ya resuelto, sin leer directamente el `Request`.

### 6.7 `DataGridExportNormalizer.php`

**Rol**

Preparar opciones de exportación/herramientas que luego consumen scope y template.

**Responsabilidades confirmadas**

- Normalizar formatos exportables definidos como string o array.
- Crear links para export con `export=csv` o `export=xls`.
- Limpiar `page` al construir export links.
- Agregar opción `print` cuando `print_enabled` está activo.
- Asignar iconos por defecto por formato.
- Devolver entradas con `format`, `label`, `icon`, `href`, `attributes`, `is_print`.

**Cambio visual observado**

El runtime actual usa un dropdown `Tools` en el template en lugar de botones aislados.

**Pendiente de verificación**

- El prompt atribuye al normalizer la estructura final del dropdown. En el código actual, `DataGridExportNormalizer` prepara los items; la estructura visual final del dropdown se arma en `boot-core/template/scope/components/_admin-datagrid.php` y `boot-core/template/components/_admin-datagrid.phtml`.

### 6.8 `DataGridBulkActionNormalizer.php`

**Rol**

Preparar acciones masivas para render.

**Responsabilidades confirmadas**

- Normalizar `name`, `label`, `method`, `href`, `confirm`, `icon`, `variant`, `attributes`.
- Preservar query params relevantes del estado actual.
- Remover el parámetro `export` de la URL resultante.

**Pendiente de verificación**

- El prompt menciona `class` como responsabilidad directa del normalizer. En el código actual, la clase devuelve `variant`, no `class`; la clase CSS final se resuelve en el scope companion.

### 6.9 `DataGridPaginationBuilder.php`

**Rol**

Construir metadata de paginación lista para render.

**Responsabilidades confirmadas**

- `total`
- `per_page`
- `per_page_options`
- `current_page`
- `last_page`
- `from`
- `to`
- `has_pages`
- `has_previous`
- `has_next`
- `first_url`
- `prev_url`
- `next_url`
- `last_url`
- `pages`

**Cambios confirmados**

- Existe navegación `First` y `Last`.
- `per_page_options` sale de la configuración real del grid.
- La navegación preserva filtros, búsqueda, sort, direction y `per_page`.
- Si el `per_page` activo no está en la lista configurada, el builder lo agrega al set devuelto para mantener coherencia del selector.

### 6.10 `DataGridColumnNormalizer.php`

**Rol**

Preparar columnas para render.

**Responsabilidades confirmadas**

- `key`
- `label`
- `sortable`
- `sort_active`
- `sort_direction`
- `sort_icon_class`
- `sort_url`
- `header_class`
- `cell_class`
- `align`
- `width`
- `raw`
- `type`
- `formatter`
- `empty`

**Comportamiento confirmado**

- Genera URLs de sort.
- Alterna `asc` y `desc`.
- Conserva query params activos.
- Reinicia la página al ordenar.
- Soporta `header_class`, `cell_class` y `class` por compatibilidad.

**Pendiente de verificación**

- La clase sí devuelve `sort_icon_class`, pero el scope companion recalcula el icono visual final para el template. Por eso, la clase de icono expuesta por el normalizer no es la única verdad de UI.

### 6.11 `DataGridRowActionNormalizer.php`

**Rol**

Preparar acciones por fila.

**Responsabilidades confirmadas**

- Evaluar visibilidad antes de incluir la acción.
- Resolver `method`.
- Resolver `href`.
- Resolver `label`.
- Resolver `icon`.
- Resolver `class`.
- Resolver `confirm`.
- Interpolar placeholders tipo `{id}`, `{slug}`, `{email}` en strings.
- Soportar `Closure` en `href`, `label`, `icon`, `class`, `confirm` y `visible`.

**Pendiente de verificación**

- El prompt lista `visible` como parte de la salida normalizada. En el código actual, la visibilidad se evalúa durante la normalización, pero no se devuelve un campo `visible` en el array final.

### 6.12 `DataGridRowNormalizer.php`

**Rol**

Preparar filas y celdas para render y export.

**Responsabilidades confirmadas**

- `normalize()`
- `resolveCellValue()`
- `stringifyStructuredValue()`
- `sanitizeExportRow()`

**Comportamiento confirmado**

- Usa `row_key` para la clave de fila y para el valor de checkbox en bulk actions.
- Soporta valores estructurados:
  - `stack`
  - `code`
  - `badge`
  - `badges`
- `booleanBadge()` se materializa como una variante estructurada de `badge` desde la fachada.
- Aplica `SensitiveDataPolicy` en exportación cuando existe `resource_key`.
- Centraliza la resolución de valores de celda para render y export.
- Convierte structured values a texto plano para CSV/XLS antes de exportar.

## 7. Cambios en la API pública

La API pública fluida del grid se mantiene. Esto es el punto más importante del refactor desde compatibilidad: el cambio se concentra en la implementación interna, no en obligar a reescribir controladores existentes.

**Métodos que se mantienen**

- `baseUrl()`
- `title()`
- `emptyState()`
- `columns()`
- `filters()`
- `actions()`
- `bulkActions()`
- `exportFormats()`
- `resourceKey()`
- `rowKey()`
- `defaultSort()`
- `pagination()`
- `searchPlaceholder()`
- `provider()`
- `printEnabled()`
- `resolve()`
- `exportCsv()`
- `export()`

**Métodos públicos relevantes añadidos o consolidados como frontera actual**

- `export()` como mecanismo general de exportación.
- Helpers estáticos estructurados:
  - `stack()`
  - `code()`
  - `badge()`
  - `badges()`
  - `booleanBadge()`

**Métodos deprecated**

- `exportCsv()` debe considerarse compatibilidad temporal por diseño del refactor.

**Pendiente de verificación**

- El comportamiento wrapper de `exportCsv()` está confirmado, pero la marca formal `@deprecated` todavía no aparece implementada en el código revisado.

**Por qué `rowKey()` no debe eliminarse**

`rowKey()` no es ruido interno: es parte de la API pública fluida para grids cuyo identificador de fila no es `id`. El código actual usa `row_key` durante la normalización de filas, incluyendo la clave visual de la fila y los checkboxes de bulk actions. Eliminarlo rompería grids que usan identificadores de dominio como `uuid`, `slug`, `permission_id`, `code` o `external_id`.

**Por qué `exportCsv()` queda como compatibilidad**

El código actual confirma que `exportCsv()` redirige a `export()`. Eso preserva controladores anteriores que solo conocían CSV y, al mismo tiempo, permite que la frontera nueva sea `export()` para `csv` y `xls`.

## 8. Cambios visuales en el template

El render del grid ya no usa el header como zona de acumulación de controles. En `boot-core/template/components/_admin-datagrid.phtml`, el header quedó reducido a identidad del grid:

- título;
- subtítulo.

Controles removidos del header:

- dropdown `Tools`;
- chip/resumen de records.

En su lugar, el runtime actual muestra una toolbar superior entre filtros y tabla, y otra toolbar inferior en el footer. Ambas incluyen:

- dropdown `Tools`;
- selector `Per page`;
- resumen `Showing X-Y of Z`;
- paginación con `First`, `Previous`, páginas, `Next` y `Last`.

Esto mejora la navegación en tablas largas porque el operador puede exportar, imprimir, cambiar `per_page` o paginar tanto antes como después de revisar la tabla.

**Detalle importante del código real**

- La plantilla visual se apoya en `boot-core/template/scope/components/_admin-datagrid.php` para preparar `grid_exports`, `grid_per_page_options`, resumen y metadata de paginación.
- El template mantiene dos formularios separados para `per_page`, uno arriba y otro abajo.
- No hay `onclick` ni `onchange` inline en el template revisado.

**Complemento JS confirmado**

`public/assets/js/catalyst/modules/admin-grid.js` activa el comportamiento del grid sobre cualquier nodo con `data-admin-grid`, incluso si el grid no tiene bulk actions, porque ahora `print` y `per_page` también dependen del mismo módulo.

## 9. Exportación CSV/XLS/Print

### CSV

La exportación CSV actual es real y usa `fputcsv` a través de `DataGridCsvExporter`. La fachada `DataGrid` resuelve headers y filas exportables y delega la escritura física del stream.

Puntos confirmados:

- los headers se derivan de las columnas normalizadas;
- las filas se construyen desde columnas configuradas y datos normalizados por `DataGridRowNormalizer`;
- cada línea se agrega al array con `$csvRows[] = $line;`;
- antes de serializar, el grid convierte structured values a texto plano y aplica `strip_tags()`.

### XLS

La exportación `xls` actual no es `XLSX` nativo. El código genera una tabla HTML y la sirve con MIME `application/vnd.ms-excel`, lo cual es suficiente como export Excel-compatible para la fase alpha actual.

Puntos confirmados:

- el archivo generado termina en `.xls`;
- las celdas exportadas pasan por `strip_tags()` y luego por `htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`;
- no existe integración actual con `PhpSpreadsheet`.

### Print

La opción `print` vive dentro del dropdown `Tools` y no abre un endpoint dedicado. El template la renderiza como botón con `data-grid-print` y `admin-grid.js` ejecuta `window.print()` desde JS externo.

**Pendiente de verificación**

- `DataGridExportNormalizer` reconoce icono por defecto para `xlsx`, pero el método `DataGrid::export()` revisado solo confirma soporte efectivo para `csv` y `xls`.

## 10. Bug de `per_page` corregido

La causa raíz del bug era una incoherencia entre las opciones visibles del selector y las opciones reales permitidas por el grid.

Escenario problemático:

- el frontend podía mostrar un fallback como `10 / 25 / 50`;
- pero un grid específico podía estar configurado con `pagination(20, [20, 50, 100])`;
- cuando el usuario elegía `10` o `25`, `DataGridStateResolver` rechazaba ese valor por no pertenecer a `per_page_options` y volvía al default `20`.

La corrección observable en el código actual es:

- `DataGridStateResolver` valida `per_page` contra las opciones reales del grid;
- `DataGridPaginationBuilder` devuelve `per_page_options` desde la configuración efectiva;
- `boot-core/template/scope/components/_admin-datagrid.php` construye el selector usando `pagination['per_page_options']`, no un fallback inventado;
- los formularios superior e inferior de `per_page` reinician `page` a `1` y preservan el resto del contexto útil.

## 11. Consideraciones de seguridad

El refactor actual mejora la coherencia del grid con las reglas de seguridad del proyecto, especialmente en CSP y export.

**CSP y JS**

- El template revisado no usa `onclick` ni `onchange` inline.
- `print` y `per_page` se resuelven desde `public/assets/js/catalyst/modules/admin-grid.js`.
- El componente se activa mediante `data-*`, alineado con el patrón general del proyecto.

**Sanitización para export**

- `DataGridRowNormalizer::sanitizeExportRow()` aplica `SensitiveDataPolicy` cuando el grid declara `resource_key`.
- En CSV, el grid convierte valores estructurados a texto plano y aplica `strip_tags()`.
- En XLS HTML-compatible, además de `strip_tags()`, el contenido visible se escapa con `htmlspecialchars()`.

**Structured values**

- El render visual de `stack`, `code`, `badge` y `badges` queda encapsulado en el grid y el parcial `_admin-datagrid-cell.phtml`, lo que reduce la necesidad de HTML inline fabricado desde controladores.

**Resultado verificado**

- `php public/cli.php security:check` reportó `Hard failures: none` y `Warnings: none`.
- El barrido textual de `onchange=`, `onclick=` y `javascript:` no mostró uso nuevo en el template del grid; el único match textual observado en la verificación ejecutada corresponde al código fuente de `SecurityCheckCommand.php`, no a una vista runtime.

## 12. Comandos de validación

Comandos PowerShell documentados para esta revisión:

```powershell
Get-ChildItem -Path app\Framework\Admin\Grid -Filter DataGrid*.php |
    ForEach-Object {
        Write-Host "Linting $($_.Name)"
        php -l $_.FullName
    }

composer dump-autoload -o

php public\cli.php inspect:lint

php public\cli.php route:list

php public\cli.php security:check

Get-ChildItem -Path boot-core,Repository,app,public -Recurse -Include *.php,*.phtml |
    Select-String -Pattern 'onchange=','onclick=','javascript:' |
    Select-Object Path, LineNumber, Line

Select-String -Path app\Framework\Admin\Grid\DataGrid.php `
    -Pattern "private function normalizeActionsForRow","private function resolveCellValue","private function stringifyStructuredValue","private function sanitizeExportRow","private function interpolate","SensitiveDataPolicy"

Select-String -Path app\Framework\Admin\Grid\DataGrid.php `
    -Pattern "rowNormalizer->sanitizeExportRow","rowNormalizer->resolveCellValue","rowNormalizer->stringifyStructuredValue"
```

**Resultado de la verificación ejecutada en esta revisión**

- Lint PHP de `DataGrid*.php`: OK, sin errores de sintaxis.
- `composer dump-autoload -o`: OK.
- `php public\cli.php inspect:lint`: OK, `Structural lint is coherent`.
- `php public\cli.php route:list`: OK, `222 route(s) listed`.
- `php public\cli.php security:check`: OK, sin hard failures ni warnings.
- Búsqueda de handlers inline: sin hallazgos runtime nuevos del grid; se observó un match textual en `app/Framework/Cli/Commands/SecurityCheckCommand.php` por el propio código del verificador.
- Búsqueda de helpers privados antiguos en `DataGrid.php`: sin matches.
- Búsqueda de delegación a `rowNormalizer` en `DataGrid.php`: matches presentes en `exportCsvRows()` y `exportXlsRows()`.

## 13. Checklist funcional

- [ ] Audit Log carga y renderiza el grid correctamente.
- [ ] Roles carga y renderiza el grid correctamente.
- [ ] Permissions carga y renderiza el grid correctamente.
- [ ] Media Library carga y renderiza el grid correctamente.
- [ ] Metadata Fields carga y renderiza el grid correctamente.
- [ ] Documents carga y renderiza el grid correctamente.
- [ ] Automation carga y renderiza el grid correctamente.
- [ ] Las columnas aparecen correctamente.
- [ ] El sort funciona en asc y desc.
- [ ] Los filtros funcionan.
- [ ] La búsqueda funciona.
- [ ] `Per page` funciona arriba.
- [ ] `Per page` funciona abajo.
- [ ] `First` y `Last` funcionan.
- [ ] `Previous` y `Next` funcionan.
- [ ] El dropdown `Tools` aparece cuando corresponde.
- [ ] Exportar CSV descarga archivo con headers y filas.
- [ ] Exportar XLS descarga archivo compatible con Excel/LibreOffice.
- [ ] `Print` abre el diálogo de impresión.
- [ ] Bulk actions siguen funcionando.
- [ ] Acciones por fila siguen funcionando.
- [ ] Las confirmaciones siguen funcionando.
- [ ] `badge`, `badges`, `stack` y `code` siguen renderizando correctamente.
- [ ] La exportación convierte structured values a texto plano.
- [ ] `SensitiveDataPolicy` se aplica en export cuando existe `resource_key`.
- [ ] No se introduce JS inline nuevo.
- [ ] No se rompe CSP.

## 14. Riesgos o puntos pendientes

- `xls` actual es Excel-compatible vía HTML; no es `XLSX` real.
- `exportCsv()` puede retirarse en una versión futura, pero hoy sigue siendo útil como compatibilidad temporal.
- `rowKey()` debe mantenerse como API pública porque participa en identificación de fila y bulk actions.
- `DataGrid` ya quedó suficientemente desacoplado para esta fase; no hay evidencia en el código revisado de que siga necesitando subdivisión adicional salvo que aparezca un bug real o una nueva frontera clara.
- Un futuro paso posible sería integrar una librería de `XLSX` real, pero no forma parte del refactor confirmado actual.
- `DataGridExportNormalizer` reconoce iconografía para `xlsx`, pero el soporte exportable confirmado por `DataGrid::export()` hoy es `csv` y `xls`.
- `DataGridBulkActionNormalizer` devuelve `variant`, mientras que la clase CSS final de botón se resuelve en el scope companion. Si se espera mover esa responsabilidad al normalizer, queda pendiente de verificación.
- `DataGridRowActionNormalizer` evalúa visibilidad, pero no expone un campo `visible` en la salida final; si otra documentación histórica afirma lo contrario, debe revisarse.

## 15. Conclusión

El refactor dejó `DataGrid` en un estado sensiblemente más mantenible dentro del contexto real de Catalyst. La clase principal ya no carga sola con estado, URLs, paginación, exportación y rendering estructurado; ahora coordina colaboradores especializados y preserva la API fluida que consumen los controladores existentes.

Para una alpha local en evolución hacia RC, el resultado es adecuado: mejor separación de responsabilidades, menor riesgo de tocar sorting/export/paginación sin romper todo el componente, y una superficie visual más coherente con CSP y con la navegación administrativa del proyecto. No convierte el componente en una capa cerrada o definitiva, pero sí lo deja suficientemente limpio y trazable para la siguiente etapa del framework.
