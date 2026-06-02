# Auditoria inicial de arquitectura - Fase 6

Fecha: 2026-06-01
Estado: auditoria inicial generada / pendiente de decision del usuario
Regla de esta pasada: generar evidencia antes de modificar runtime o borrar archivos.

## Reportes de seguimiento 6A

La auditoria inicial se amplio en etapas ordenadas de solo lectura:

- `docs/audits/architecture-first/2026-06-01-6a1-bootstrap-routing-middleware.md`
- `docs/audits/architecture-first/2026-06-01-6a2-framework-modules.md`
- `docs/audits/architecture-first/2026-06-01-6a3-app-surfaces.md`
- `docs/audits/architecture-first/2026-06-01-6a4-documentation-debt.md`

Estos reportes sustituyen la lista inicial de remediacion. La primera prioridad
runtime es la tarea `6B.0` del roadmap.

## Resumen ejecutivo

No se detectaron colisiones exactas de rutas ni una exposicion nueva de DevTools.
El runtime registra 273 filas de rutas, 439 combinaciones metodo-ruta y 18
modulos. Las 45 filas de rutas de prueba bajo `/test-layout`, `/uml` y
`/test-features*` conservan `DevToolsGuardMiddleware`.

La auditoria identifica cinco decisiones arquitectonicas relevantes:

1. Las rutas globales `/index` y `/index.php` dependen de un controlador ubicado
   dentro de DevTools.
2. El inventario de modulos combina manifiestos distribuidos con tres
   declaraciones internas y no representa el ownership de `POST /flash/dismiss`.
3. La documentacion caliente conserva rutas previas al cutover.
4. `Repository/App/Surface/` mezcla modulos, soporte compartido y un comando CLI
   de desarrollo; ademas conserva clases sin consumidores detectados.
5. Algunos controladores de framework acumulan web, API, presentacion y estado
   temporal en una misma clase.

No se recomienda cerrar la fase 6 hasta decidir la remediacion inmediata y la
deuda que debe quedar documentada.

## Alcance revisado

- Frontera entre core, modulos framework, superficies app y demos.
- Ownership de rutas y colisiones exactas metodo-URI.
- Registro y descubrimiento de modulos.
- DevTools: guardas, rutas y dependencias cruzadas.
- Clases repetidas, archivos PHP identicos y controladores grandes.
- Documentacion caliente frente al runtime actual.
- Catalogo generado `docs/runtime-module-catalog.md`.

## Hallazgos ordenados por severidad

No se detectaron hallazgos criticos ni altos.

### Medio - ARQ-01: rutas globales acopladas a DevTools

**Archivo/linea**

- `boot-core/routes/global-routes.php:34`
- `boot-core/routes/global-routes.php:66`
- `boot-core/routes/global-routes.php:67`
- `Repository/Framework/DevTools/Controllers/RouteTestController.php:36`

**Evidencia breve**

Las rutas globales cacheables `/index` y `/index.php` usan
`Catalyst\Repository\DevTools\Controllers\RouteTestController@redirectToRoot`.
Su comportamiento es transversal: redirigir a `/` con HTTP 301.

**Impacto**

El bootstrap global depende de una clase ubicada en el modulo de herramientas
de desarrollo. Esto mezcla una responsabilidad de core con DevTools y dificulta
retirar o aislar ese modulo.

**Recomendacion**

Mover la accion de redireccion a un controlador transversal de framework o a
una pieza equivalente de core. Mantener las rutas y el HTTP 301.

**Decision sugerida**

Corregir ahora mediante una spec pequena y prueba de rutas.

### Medio - ARQ-02: ownership hibrido e incompleto de rutas/modulos

**Archivo/linea**

- `app/Framework/Module/ModuleRegistry.php:19`
- `app/Framework/Module/ModuleRegistry.php:20`
- `app/Framework/Module/ModuleRegistry.php:63`
- `app/Framework/Module/ModuleRegistry.php:200`
- `app/Framework/Module/ModuleRegistry.php:279`
- `app/Framework/Module/ModuleRegistry.php:287`
- `boot-core/routes/global-routes.php:70`

**Evidencia breve**

`framework.auth`, `framework.devtools` y `framework.notification` se declaran
dentro de `ModuleRegistry`, mientras otros modulos usan `module.php`. El
inspector combina descubrimiento, declaracion interna y manifiesto. Tras
expandir los metodos, existe una sola ruta runtime sin owner:
`POST /flash/dismiss`.

**Impacto**

Hay mas de una fuente de verdad para metadatos de modulo y los concerns
globales no tienen representacion explicita en el inventario. Esto aumenta la
posibilidad de desalineacion al extender el framework.

**Recomendacion**

Definir el contrato deseado antes de cambiar runtime:

- opcion A: migrar los tres built-ins a `module.php`;
- opcion B: documentar los built-ins como excepciones de core;
- en ambos casos, ensenar al inspector una categoria explicita `global` para
  endpoints transversales. No crear un modulo por inferencia terminologica:
  `shell` describe layouts/composicion visual dentro del framework.

**Decision sugerida**

Resolver el contrato ahora y ejecutar la implementacion en una spec separada.

### Medio - ARQ-03: documentacion caliente desalineada del cutover

**Archivo/linea**

- `docs/architecture.md:29`
- `docs/architecture.md:34`
- `STRUCTURE.md:6`
- `STRUCTURE.md:45`
- `STRUCTURE.md:46`
- `STRUCTURE.md:47`
- `STRUCTURE.md:48`
- `STRUCTURE.md:127`
- `STRUCTURE.md:128`
- `STRUCTURE.md:535`
- `TERMINAL.md:152`
- `TERMINAL.md:153`
- `docs/repository-devtools.md:12`
- `docs/repository-devtools.md:100`
- `docs/repository-devtools.md:268`

**Evidencia breve**

La documentacion caliente conserva referencias a `/setup`,
`/operations/module-designer`, `/operations/localization`, `/media-library`,
`/media-fields`, `/document-templates` y aliases
`/test-features/module-designer*`. El runtime usa familias
`/configuration/*` y `/workspaces/*`; DevTools ya no registra los aliases del
disenador. `docs/architecture.md` tambien enumera solo una parte de los modulos
framework y presenta `app/Framework/` como inmodificable sin distinguir
mantenimiento del framework frente a extensiones app.

**Impacto**

Los documentos que deben orientar a humanos y agentes inducen implementaciones
contra rutas retiradas y una frontera core ambigua.

**Recomendacion**

Actualizar los documentos calientes contra `route:list --json` y el catalogo
runtime. Conservar snapshots historicos solo cuando esten marcados como tales.

**Decision sugerida**

Corregir ahora como remediacion documental de bajo riesgo.

### Medio - ARQ-04: clasificacion ambigua dentro de `Repository/App/Surface`

**Archivo/linea**

- `app/Framework/Module/ModuleDiscovery.php:27`
- `app/Framework/Module/ModuleDiscovery.php:43`
- `app/Framework/Cli/CliKernel.php:181`
- `app/Framework/Cli/CliKernel.php:187`
- `Repository/App/Surface/Demo/Controllers/AppDemoController.php:11`
- `Repository/App/Surface/Demo/Commands/ExportDevelopmentOverlayCommand.php:13`
- `Repository/App/Surface/PublicSupport/Controllers/PublicPageController.php:12`
- `Repository/App/Surface/PublicSupport/Support/PublicNavigationBuilder.php:10`
- `Repository/App/Surface/PublicSupport/Support/PublicRuntimeSnapshot.php:12`

**Evidencia breve**

`Surface/Demo` no tiene `module.php`, `routes.php` ni vistas; por tanto no
aparece como modulo. Sin embargo, su comando `dev:export-overlay` se
autodescubre de forma intencional. `AppDemoController` no tiene consumidor
detectado. `Surface/PublicSupport` tampoco es modulo: actua como soporte
compartido para Home, Landing y Store. `PublicNavigationBuilder` y
`PublicRuntimeSnapshot` no tienen consumidores detectados.

**Impacto**

La carpeta `Surface/` no comunica de forma consistente si contiene modulos
instalables, librerias internas o herramientas de desarrollo. El codigo sin
consumidores complica inventario y onboarding.

**Recomendacion**

Definir una spec de clasificacion:

- conservar `dev:export-overlay` como herramienta CLI de desarrollo;
- decidir una ubicacion explicita para soporte app compartido;
- borrar clases sin consumidores solo despues de confirmacion y verificacion.

**Decision sugerida**

Decidir ahora el criterio; aplicar movimientos o borrados en una remediacion
separada porque pueden afectar namespaces.

### Medio - ARQ-05: controladores con demasiadas responsabilidades

**Archivo/linea**

- `Repository/Framework/Automation/Controllers/AutomationRuleController.php:45`
- `Repository/Framework/Automation/Controllers/AutomationRuleController.php:364`
- `Repository/Framework/Automation/Controllers/AutomationRuleController.php:448`
- `Repository/Framework/Documents/Controllers/DocumentTemplateController.php:41`
- `Repository/Framework/Documents/Controllers/DocumentTemplateController.php:320`
- `Repository/Framework/Documents/Controllers/DocumentTemplateController.php:402`
- `Repository/Framework/DemoUi/Controllers/DemoUiController.php:513`

**Evidencia breve**

`AutomationRuleController` tiene 731 lineas y combina CRUD web, API, ejecucion,
render y estado temporal. `DocumentTemplateController` tiene 624 lineas y
combina CRUD web, API, preview/export, render y estado temporal.
`DemoUiController` tiene 1234 lineas, pero su manifiesto lo identifica como
baseline congelado de referencia UI.

**Impacto**

Automation y Documents tienen mayor costo de cambio y pruebas. DemoUi es grande
por su funcion de catalogo congelado y no debe refactorizarse incidentalmente.

**Recomendacion**

Dejar DemoUi estable. Abrir specs posteriores para separar limites web/API y
servicios de presentacion/estado en Automation y Documents con pruebas de
regresion.

**Decision sugerida**

Registrar como deuda posterior; no mezclar este refactor con la remediacion
inmediata de fase 6.

### Bajo - ARQ-06: scope companions identicos en Settings

**Archivo/linea**

- `Repository/Framework/Settings/Views/scope/partials/_settings-dkim-card.php:8`
- `Repository/Framework/Settings/Views/scope/partials/_settings-setup-actions.php:8`

**Evidencia breve**

Ambos archivos PHP son identicos y solo exponen el campo CSRF como
`TrustedHtml`.

**Recomendacion**

Mantener por ahora si el contrato de scopes exige companion por parcial.
Consolidar solo si aparece un patron repetido que justifique un helper comun.

**Decision sugerida**

Dejar como deuda baja.

### Bajo - ARQ-07: nombre corto `Validator` duplicado

**Archivo/linea**

- `app/Framework/Argument/Validator.php:38`
- `app/Helpers/Validation/Validator.php:37`

**Evidencia breve**

Existen dos clases `Validator` bajo namespaces distintos.

**Recomendacion**

No renombrar sin necesidad funcional. Mantener imports explicitos y registrar
la ambiguedad para futuras extensiones.

**Decision sugerida**

Aceptar como deuda baja.

## Evidencia positiva

- `quality:check`: PASS.
- `composer validate --strict`: PASS.
- `composer audit`: PASS sin advisories.
- `route:lint`: PASS.
- `inspect:lint`: PASS.
- `security:check`: PASS sin hard failures ni warnings.
- `status`: Ready con warnings DNS esperados desde host para servicios Docker.
- Rutas: 273 filas; 439 combinaciones metodo-ruta; 0 colisiones exactas.
- Ownership: una ruta global sin owner de modulo, `POST /flash/dismiss`.
- DevTools: 45 filas protegidas; 0 sin `DevToolsGuardMiddleware`.
- Catalogo generado: coincide con `docs/runtime-module-catalog.md`; la unica
  diferencia al regenerar por stdout es `Last generated`.
- Clases PHP: 549 declaraciones; un nombre corto duplicado.
- PHP identicos fuera de `vendor/`: un grupo de dos scope companions Settings.

## Archivos revisados

- `AGENTS.md`
- `docs/harness-context-map.md`
- `docs/superpowers/plans/2026-06-01-catalyst-stabilization-roadmap.md`
- `docs/architecture.md`
- `docs/runtime-module-catalog.md`
- `STRUCTURE.md`
- `TERMINAL.md`
- `boot-core/routes/global-routes.php`
- `app/Framework/Module/ModuleRegistry.php`
- `app/Framework/Module/ModuleDiscovery.php`
- `app/Framework/Cli/CliKernel.php`
- `Repository/Framework/DevTools/routes.php`
- `Repository/Framework/DevTools/Controllers/RouteTestController.php`
- `Repository/Framework/Operations/routes.php`
- `Repository/Framework/DemoUi/module.php`
- `Repository/Framework/DemoUi/Controllers/DemoUiController.php`
- `Repository/Framework/Automation/Controllers/AutomationRuleController.php`
- `Repository/Framework/Documents/Controllers/DocumentTemplateController.php`
- `Repository/App/Surface/Demo/`
- `Repository/App/Surface/PublicSupport/`
- `docs/repository-devtools.md`
- `docs/ui/migration-ui-refactor-cutover.md`
- `docs/ui/route-inventory-99.md`

## Comandos ejecutados y resultado resumido

```powershell
git status --short
php public/cli.php quality:check
composer validate --strict
composer audit
php public/cli.php route:list --json
php public/cli.php inspect:lint
php public/cli.php security:check
php public/cli.php status
php public/cli.php inspect:modules --json
php public/cli.php inspect:harness --json
php public/cli.php docs:sync-runtime --stdout
rg -n ...
Get-ChildItem ...
Get-FileHash ...
```

Resultado: baseline limpio antes del reporte; checks bloqueantes en PASS. Las
consultas auxiliares se usaron para ownership, guards, tamanos, duplicados y
alineacion documental sin escribir runtime.

## Riesgos que requieren decision del usuario

1. Aprobar o rechazar remediacion inmediata de ARQ-01.
2. Elegir contrato para built-ins y rutas globales en ARQ-02.
3. Aprobar alineacion documental inmediata de ARQ-03.
4. Definir si ARQ-04 se resuelve ahora mediante una spec de clasificacion y
   limpieza confirmada.
5. Confirmar que ARQ-05, ARQ-06 y ARQ-07 queden como deuda posterior.

## Proximo paso recomendado

Preparar una spec de remediacion acotada para ARQ-01, ARQ-02 y ARQ-03. Mantener
ARQ-04 como decision separada porque puede implicar movimientos o borrados.
Registrar ARQ-05, ARQ-06 y ARQ-07 como deuda si el usuario lo confirma.

La fase 6 no se marca como completada con este reporte.
