# Phase 5 Security Hardening Specification

## Estado

Especificacion aprobada para planificacion. Implementacion pendiente.

## Resumen ejecutivo

La fase 5 endurecera la superficie publica de Catalyst sin redisenar licencias ni
eliminar herramientas de desarrollo utiles. El trabajo conserva HTML y texto como
formatos internos de plantillas y pruebas CLI, pero reserva
`public/generated-documents/` para PDF y `public/generated-reports/` para CSV/XLS.

Los artefactos auxiliares de pruebas CLI se moveran a almacenamiento privado bajo
`boot-core/storage/runtime/`. `public/uploads/devtools/` se conserva porque forma
parte de una herramienta de prueba deliberadamente publica en development.

La fase tambien cerrara el riesgo real detectado en Documents: el preview actual
convierte una plantilla editable a `TrustedHtml` sin saneamiento HTML.

## Historia de usuario

Como mantenedor de Catalyst, quiero separar claramente artefactos publicos,
artefactos privados de pruebas y HTML confiable para que el framework siga siendo
util durante desarrollo sin publicar archivos inesperados ni renderizar HTML
editable inseguro.

## Objetivo

1. Mantener publicos solo los formatos esperados por cada superficie.
2. Evitar que pruebas CLI dejen TXT o HTML auxiliares bajo webroot.
3. Bloquear descarga HTTP de archivos INI sin moverlos.
4. Sanear previews HTML editables antes de convertirlos a `TrustedHtml`.
5. Retirar el bypass legado `DataGrid` raw si no tiene consumidores activos.

## Problema que resuelve

- Documents persiste actualmente `.html` y `.txt` en `public/generated-documents/`.
- Reporting acepta formato solicitado, pero el worker persiste siempre CSV.
- Los smoke CLI limpian filas de base de datos de forma best-effort, pero pueden
  dejar archivos fisicos en `public/smoke/` y documentos HTML en webroot.
- Apache permite servir archivos existentes antes de pasar por el router.
- Documents inserta previews HTML editables mediante `TrustedHtml` sin allowlist.

## Decisiones confirmadas

1. `APP_KEY` queda fuera del alcance de esta fase. Se mantiene como preparacion
   para el futuro control de licencias.
2. `display_errors On` se conserva durante desarrollo. Su endurecimiento se
   programa como requisito previo a `v1.0.0`.
3. `public/.user.ini` y `public/php.ini` permanecen en su ubicacion actual porque
   soportan runtimes distintos.
4. Apache debe negar su descarga HTTP.
5. `public/uploads/devtools/` se conserva como superficie de prueba util.
6. HTML y TXT pueden existir para preview o pruebas CLI, pero no como documentos
   descargables normales dentro de `public/generated-documents/`.
7. No se borran artefactos locales existentes sin confirmacion explicita.
8. No se agregan dependencias Composer.

## Alcance incluido

- Documents: export persistido normal exclusivamente PDF.
- Reporting: persistencia CSV o XLS segun formato solicitado.
- Storage: disco local privado `runtime` bajo `boot-core/storage/runtime/`.
- Smoke CLI: archivos auxiliares TXT/HTML fuera de `public/` y limpieza fisica.
- Apache: denegacion explicita de INI y dotfiles sensibles.
- Trusted HTML: saneamiento allowlist de preview Documents y retiro del bypass
  legado raw de DataGrid sin consumidores.
- Documentacion, regresiones CLI y quality gate.

## Alcance no incluido

- Implementacion de licencias o rediseño de `APP_KEY`.
- Cambio de `display_errors On` antes de preparar `v1.0.0`.
- Eliminacion de `public/uploads/devtools/`.
- Limpieza fisica de residuos existentes.
- Migracion completa de descargas publicas a controladores autorizados.
- Dependencias externas de saneamiento HTML.

## Actores

- Administrador autorizado que edita, previsualiza y exporta plantillas.
- Worker de reporting.
- Desarrollador que ejecuta smoke tests CLI.
- Usuario de DevTools autorizado en development.
- Servidor Apache que aplica reglas de acceso directo.

## Precondiciones

- El proyecto esta configurado.
- El disco `local` publico continua apuntando a `public/`.
- El nuevo disco `runtime` privado apunta a `boot-core/storage/runtime/`.
- DevTools permanece protegido por `DevToolsGuardMiddleware`.

## Reglas funcionales

### Documents

1. La plantilla puede conservar formato interno `html`, `text` o `pdf`.
2. Preview no crea archivos persistidos.
3. Export normal crea siempre un PDF bajo `public/generated-documents/{slug}/`.
4. `DocumentArtifact.format` del export normal queda como `pdf`.
5. Si una prueba CLI requiere persistir HTML o TXT auxiliar, usa disco `runtime`.

### Reporting

1. Formatos persistidos permitidos: `csv`, `xls`.
2. El worker debe rechazar formatos distintos con error controlado.
3. CSV usa `DataGrid::exportCsvRows()`.
4. XLS usa `DataGrid::exportXlsRows()`.
5. La extension, MIME, nombre y contenido deben corresponder al formato.

### Smoke CLI

1. `attachments:smoke`, `catalogs:smoke`, `reporting:smoke` y
   `retention:smoke` se conservan.
2. Archivos auxiliares se escriben con `disk=runtime` bajo `smoke/...`.
3. Cleanup elimina registros y archivos fisicos creados por la prueba.
4. Cleanup sigue siendo best-effort, pero registra warning si deja residuos.

### INI

1. Los archivos INI permanecen bajo `public/`.
2. Apache niega acceso HTTP a `.user.ini`, `php.ini`, `*.ini` y dotfiles
   sensibles.
3. El bloqueo HTTP no debe impedir que PHP/FPM lea su configuracion.

### TrustedHtml

1. Preview Documents sanea HTML renderizado mediante allowlist antes de usar
   `TrustedHtml`.
2. Se eliminan `<script>`, `<style>`, handlers `on*=`, URLs `javascript:`,
   atributos `style` y elementos activos no aprobados.
3. Se permiten etiquetas de documento basicas: encabezados, parrafos, listas,
   tablas, enlaces seguros, enfasis y contenedores simples.
4. `DataGrid` deja de aceptar HTML crudo legado porque no tiene consumidores
   activos detectados.
5. Permanecen aceptados los `TrustedHtml` server-owned: CSRF fields, JSON
   generado con `InlineJson`, nonce attrs, layouts ya renderizados, DevTools
   protegidos y previews DemoUi leidos de archivos repo-locales allowlisted.

## Matriz TrustedHtml

| Grupo | Origen | Estado | Accion |
|---|---|---|---|
| CSRF, nonce, JSON islands | Helpers internos | Aceptado | Mantener |
| Layout content renderizado | View engine | Aceptado | Mantener |
| DevTools modal/toaster/UML | Archivos y parciales internos protegidos | Aceptado | Mantener |
| DemoUi theme previews | Archivos repo-locales seleccionados por catalogo | Aceptado | Mantener |
| FormBuilder attrs | Arrays normalizados y valores escapados | Aceptado con regresion | Mantener |
| DataGrid raw legacy | Flag sin consumidores activos | Innecesario | Retirar |
| Documents preview | Plantilla editable + payload | Riesgo confirmado | Sanear antes de `TrustedHtml` |

## Happy Path

1. Un administrador crea una plantilla HTML interna.
2. Preview renderiza el contenido saneado sin crear archivo.
3. Export genera un PDF bajo `public/generated-documents/{slug}/`.
4. Un reporte CSV genera `.csv`; un reporte XLS genera `.xls`.
5. Un smoke CLI escribe auxiliares privados bajo `boot-core/storage/runtime/smoke/`
   y los elimina al terminar.
6. DevTools upload conserva `/uploads/devtools/{uuid}.{ext}`.
7. Una solicitud HTTP a `/php.ini` o `/.user.ini` recibe denegacion.

## Flujos alternativos

- Una plantilla `text` puede previsualizar texto y exportar PDF.
- Un smoke que falla conserva salida CLI de error e intenta limpiar sus residuos.
- Reporting con `xls` genera tabla HTML compatible con Excel y extension `.xls`,
  sin agregar PhpSpreadsheet.

## Sad Path

1. Preview recibe `<script>` o `onclick`: el saneador lo elimina.
2. Preview recibe enlace `javascript:`: el saneador elimina el atributo inseguro.
3. Reporting recibe formato distinto de `csv` o `xls`: falla de forma controlada.
4. Smoke falla tras escribir archivo: cleanup intenta borrar el objeto privado y
   registra warning si no puede.
5. Acceso directo a INI: Apache responde denegado.
6. Solicitud DevTools fuera de development: `DevToolsGuardMiddleware` mantiene 403.

## BDD / Gherkin

```gherkin
Feature: Public document export boundary
  Scenario: HTML template exports only PDF artifact
    Given a document template with internal format "html"
    When an authorized administrator exports the template
    Then the persisted artifact extension is "pdf"
    And its path starts with "generated-documents/"
    And no html file is written under "public/generated-documents/"

Feature: Reporting format persistence
  Scenario Outline: Worker persists the requested supported report format
    Given a queued report with format "<format>"
    When the reporting worker processes the run
    Then the output extension is "<extension>"
    Examples:
      | format | extension |
      | csv    | csv       |
      | xls    | xls       |

Feature: Private smoke artifacts
  Scenario: CLI smoke does not publish TXT probes
    When a smoke command writes a generated TXT probe
    Then the disk is "runtime"
    And the file is stored under "boot-core/storage/runtime/smoke/"
    And cleanup removes the file

Feature: Documents preview HTML safety
  Scenario: Editable preview removes active content
    Given a template containing script tags, onclick attributes and javascript URLs
    When the preview is rendered
    Then active content is absent
    And safe structural markup remains

Feature: INI HTTP protection
  Scenario Outline: Runtime configuration cannot be downloaded
    When the browser requests "<path>"
    Then the server denies access
    Examples:
      | path       |
      | /php.ini   |
      | /.user.ini |
```

## Criterios de aceptacion

1. `public/generated-documents/` recibe solo PDF en nuevos exports normales.
2. `public/generated-reports/` recibe solo CSV o XLS.
3. `public/smoke/` no recibe nuevos archivos.
4. Smoke CLI usa almacenamiento privado y limpia archivos fisicos.
5. `public/uploads/devtools/` sigue funcionando.
6. Apache bloquea descarga de INI.
7. Preview Documents elimina contenido HTML activo.
8. No quedan consumidores activos de DataGrid raw HTML.
9. `php public/cli.php security:check` pasa.
10. `php public/cli.php security:regression` cubre saneamiento.
11. `php public/cli.php quality:check` pasa.
12. No se borran residuos previos sin autorizacion.

## Riesgos

- Cambiar Documents a export PDF puede afectar automatizaciones que esperaban URL
  HTML. Debe verificarse `AutomationManager`.
- El saneador HTML local debe ser estricto y pequeño; no reemplaza una libreria
  completa para HTML arbitrario.
- XLS sigue siendo tabla HTML compatible con Excel, no XLSX real.
- Smoke DB-backed puede requerir ejecucion dentro del contenedor WSDD.

## Backlog diferido

- Licencias y contrato definitivo de `APP_KEY`.
- `display_errors Off` y perfil de despliegue para `v1.0.0`.
- Descargas autorizadas por controlador si se decide privatizar exports normales.

## Definicion de listo

La implementacion puede comenzar cuando exista un plan por archivos revisado.
La fase 5 solo se marcara completada con confirmacion explicita del usuario.
