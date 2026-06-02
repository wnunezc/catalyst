# Catalyst Stabilization Roadmap Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Stabilize Catalyst as a trustworthy reusable PHP framework base after several zip-based AI edits.

**Architecture:** Work in small gated phases. Each phase must leave a Git checkpoint, update repo-local documentation, run CLI checks, and avoid touching unrelated framework behavior.

**Tech Stack:** PHP 8.4, Composer, Catalyst CLI, PowerShell, Git/GitHub, WSDD/Docker.

---

## Current Phase Map

1. **Congelar una linea base confiable** — Finalizada.
   - Git iniciado desde cero.
   - Commit baseline: `30546ad` (`baseline import`).
   - Remoto privado: `wnunezc/catalyst`.

2. **Separar codigo fuente de basura operacional** — Finalizada.
   - Assets Inspinia usados por runtime consolidados bajo `public/assets/vendor/inspinia/`.
   - Branding institucional estatico eliminado.
   - `MigrationUi` alineado como `DemoUi`.
   - Config templates sanitizados creados.

3. **Inventario inicial** — Finalizada junto con fase 2.
   - Evidencia en `docs/audits/source-operational-cleanup/`.
   - Plan detallado en `docs/superpowers/plans/2026-06-01-phase-2-source-operational-cleanup.md`.

4. **Fortalecer checks automaticos** — Finalizada.
   - Crear una puerta local estandar de calidad.
   - Reducir dependencia de memoria manual.
   - Documentar que checks bloquean cambios.
   - Commit: `c301b05` (`add quality gate command`).

5. **Auditar seguridad primero** — Completada por confirmacion explicita del usuario.
   - Secretos, `.env`, `secrets.json`, DKIM, uploads publicos.
   - CSRF/throttle, middleware admin, PHP expuesto en `public`.
   - Evidencia: `docs/audits/security-first/2026-06-01-security-first-audit.md`.
   - Hardening consolidado: `903019b` (`implement phase 5 security hardening`).

6. **Auditar arquitectura y normalizar deuda tecnica** — En ejecucion.
   - Core framework vs demo/app ejemplo.
   - Rutas sin duenio, clases duplicadas, controladores grandes.
   - Docs desalineadas y deuda DevTools.
   - Evidencia: `docs/audits/architecture-first/2026-06-01-architecture-first-audit.md`.
   - Regla confirmada: `shell` describe layouts/composicion visual; no asumir que
     representa un modulo faltante.

7. **Flujo futuro sin zips directos** — Pendiente.
   - Checklist para revisar zips/parches en carpeta temporal.
   - Setup de primer arranque para otro desarrollador.
   - Instalacion reproducible como base para otros proyectos.

---

## Phase 4: Fortalecer Checks Automaticos

### Objective

Crear un flujo local reproducible que ejecute los checks principales con una sola orden y documente claramente que fallos bloquean commit/push.

### Files

- Inspect before editing:
  - `app/Framework/Cli/Commands/`
  - `app/Framework/Cli/CommandRegistry.php` or equivalent command registration point.
  - `public/cli.php`
  - `docs/helpers-config.md`
  - `README.md`
- Candidate create:
  - `app/Framework/Cli/Commands/QualityCheckCommand.php`
  - `docs/quality-gate.md`
- Candidate modify:
  - CLI registry file that registers commands.
  - `README.md`
  - `docs/harness-context-map.md` if docs routing changes.
  - `STRUCTURE.md` if a new command class is added.

### Required Checks

The quality gate should include, at minimum:

```powershell
composer validate --strict
composer audit
php public/cli.php route:lint
php public/cli.php inspect:lint
php public/cli.php security:check
php public/cli.php status
```

### Local Warning Policy

Document expected local warnings separately from blockers:

- `php public/cli.php status` may warn from host about Docker-only DNS such as `WSDD-MySql-Server`.
- Security or routing failures are blockers.
- Composer validation/audit failures are blockers unless explicitly documented with a temporary exception.

### Execution Tasks

- [x] **Task 4.1: Close phase 2+3 Git checkpoint**

Run:

```powershell
composer dump-autoload
composer validate --strict
php public/cli.php route:lint
php public/cli.php inspect:lint
php public/cli.php status
git diff --check
git add -A
git diff --cached --check
git commit -m "finalize source operational cleanup"
git push
```

Expected:

- Composer and Catalyst CLI checks pass.
- `status` may show environment warnings only.
- Commit is created without AI co-author metadata.
- Push updates the private GitHub repository.

- [x] **Task 4.2: Discover existing CLI command patterns**

Run:

```powershell
rg -n "class .*Command|security:check|route:lint|inspect:lint|status|help" app public boot-core Repository
```

Expected:

- Identify the CLI command interface, registration mechanism, and output conventions before adding anything.

- [x] **Task 4.3: Decide whether `quality:check` should be code or docs-only**

Decision rule:

- If commands are easy to compose from the current CLI command framework without invoking shell-specific behavior, implement `quality:check`.
- If the CLI framework has no clean way to compose commands, create a docs-only quality gate first and defer command implementation.

- [x] **Task 4.4: Implement or document the quality gate**

Implementation path:

- Create `QualityCheckCommand` following existing command conventions.
- Register it in the existing CLI registry.
- Ensure it exits non-zero when a blocker check fails.

Docs-only path:

- Create `docs/quality-gate.md`.
- Add exact PowerShell commands and expected blocker/warning policy.

- [x] **Task 4.5: Verify phase 4**

Run:

```powershell
composer dump-autoload
composer validate --strict
composer audit
php public/cli.php route:lint
php public/cli.php inspect:lint
php public/cli.php security:check
php public/cli.php status
php public/cli.php help
```

If `quality:check` is implemented, also run:

```powershell
php public/cli.php quality:check
```

Expected:

- All blocker checks pass.
- Any local warning is explicitly documented.

- [x] **Task 4.6: Close phase 4 checkpoint**

Run:

```powershell
git diff --check
git status --short
```

Expected:

- Reviewable diff focused on quality gate.
- Ready for user review before commit/push unless the user explicitly asks to commit immediately.

---

## Phase 6: Auditar Arquitectura Y Normalizar Deuda Tecnica

### Objective

Razonar el flujo real del framework por superficies, detectar inconsistencias y
normalizar la arquitectura sin introducir abstracciones por intuicion. La
auditoria precede a cada lote de cambios y cada lote debe quedar verificable de
forma independiente.

### Engineering Rules

Aplicar durante toda modificacion de codigo:

1. SOLID: responsabilidades claras, bajo acoplamiento y extensibilidad.
2. DRY: evitar duplicacion de logica, vistas, validaciones, consultas y config.
3. KISS: preferir soluciones simples antes de agregar abstracciones.
4. DAO / Repository Pattern: separar persistencia de logica de negocio.
5. MVC estricto: controladores delgados, modelos de datos y vistas simples.
6. Separation of Concerns: separar rutas, controladores, servicios,
   middleware, vistas, helpers, configuracion y persistencia.
7. Dependency Inversion / Service Layer: mover logica de negocio a servicios.
8. Middleware Pipeline: centralizar concerns transversales.
9. PSR-4 / namespaces consistentes.
10. Validacion centralizada en Requests.
11. Escaping HTML por defecto mediante `e()` o equivalente seguro.
12. Prepared statements siempre.
13. Separacion de PHP, HTML, CSS y JS.
14. Programacion orientada a objetos.
15. Respetar CSP.
16. Usar i18n de forma obligatoria para texto visible.

### Confirmed Architecture Criteria

- `shell` es terminologia de layout/composicion visual. No es un modulo faltante.
- `boot-core/routes/global-routes.php` conserva middleware, gates y endpoints
  realmente transversales.
- Los modulos Framework y App conservan `module.php` y `routes.php`
  autocontenidos.
- `CliRouteLoader` conserva la composicion central de rutas activas.
- No centralizar todas las rutas Framework en un archivo monolitico.
- No aceptar como suficiente que una superficie "funciona": revisar flujo,
  responsabilidades, duplicidades, coherencia y documentacion.

### Execution Tasks

- [x] **Task 6A.1: Auditar bootstrap, entry points, composicion de rutas y middleware**
  - Revisar archivo por archivo el flujo web y CLI.
  - Confirmar orden de carga, dependencias, ownership y puntos transversales.
  - Registrar inconsistencias y recomendaciones sin modificar runtime.
  - Evidencia:
    `docs/audits/architecture-first/2026-06-01-6a1-bootstrap-routing-middleware.md`.
  - Bloqueante confirmado: el consumo de route cache omite el pipeline global
    de middleware. Requiere remediacion inmediata aprobada antes de continuar
    normalizaciones de runtime.

- [x] **Task 6A.2: Auditar modulos Framework por superficie**
  - Revisar Auth, Notification, Roles, Settings, Operations, Media, Documents,
    Automation, ApiPlatform, Catalogs, Audit, DemoUi y DevTools.
  - Revisar `module.php`, `routes.php`, controladores, servicios, repositories,
    modelos, vistas, assets, CSP, i18n y documentacion de clase.
  - Evidencia:
    `docs/audits/architecture-first/2026-06-01-6a2-framework-modules.md`.
  - DevTools queda diferido por decision del usuario: ordenar su deuda UML,
    manifiesto y overlay sin entrar ahora en su superficie interna.

- [x] **Task 6A.3: Auditar superficies App y soporte compartido**
  - Revisar Account, Dashboard, Home, Landing, Store, Demo y PublicSupport.
  - Separar modulos runtime, soporte compartido, CLI de desarrollo y codigo
    muerto confirmado.
  - Evidencia:
    `docs/audits/architecture-first/2026-06-01-6a3-app-surfaces.md`.

- [x] **Task 6A.4: Auditar documentacion inline y `/docs`**
  - Inventariar clases PHP y modulos JS sin documentacion util.
  - Detectar docs calientes obsoletas, docs por clase abandonadas y snapshots
    historicos que deben permanecer como evidencia.
  - Evidencia:
    `docs/audits/architecture-first/2026-06-01-6a4-documentation-debt.md`.

- [x] **Task 6B.0: Corregir bootstrap de route cache**
  - Registrar middleware global de forma incondicional antes de consumir rutas
    frias o cacheadas.
  - Registrar namespaces de vistas de modulos sin depender de ejecutar
    `routes.php`; el consumo de route cache tambien debe conservar renderizado.
  - Retirar del archivo de rutas responsabilidades transversales duplicadas.
  - Normalizar el orden de composicion documentado y ejecutado:
    global, API opcional, Framework y App.
  - Agregar regresion ejecutable para el camino productivo con route cache.

- [x] **Task 6B.1: Normalizar manifiestos Framework**
  - Migrar Auth y Notification a `module.php`.
  - Mantener migracion de DevTools ordenada como lote diferido.
  - Retirar declaraciones internas redundantes y validar todas las superficies.

- [x] **Task 6B.2: Corregir responsabilidades transversales y CLI overlay**
  - Extraer redirects globales fuera de DevTools hacia core transversal.
  - Mantener `/flash/dismiss` como endpoint transversal de core.
  - Hacer que inspectores clasifiquen rutas globales sin inventar modulos.
  - Mover `dev:export-overlay` junto a CLI/testing de framework y registrarlo
    explicitamente.

- [x] **Task 6B.3: Normalizar soporte App y purgar codigo muerto confirmado**
  - Reubicar soporte publico compartido fuera de `Surface/`; el inventario
    confirma que `PublicSupport` no es un modulo runtime.
  - Mover `dev:export-overlay` desde `Surface/Demo` hacia CLI/testing de
    framework y registrarlo explicitamente.
  - Presentar lista final antes de cualquier borrado destructivo.

- [x] **Task 6B.4: Purgar documentacion caliente obsoleta**
  - Alinear `STRUCTURE.md`, `TERMINAL.md`, `docs/architecture.md`,
    `docs/entry-points.md`, docs por clase y mapa documental.
  - Evidencia de implementacion y verificacion:
    `docs/audits/architecture-first/2026-06-01-6b-architecture-normalization.md`.
  - Estado: implementado y verificado / pendiente de revision del usuario y
    consolidacion en commit.

- [x] **Task 6C.1: Refactor MVC de Automation**
  - Aplicar TDD y separar web, API, servicios, Requests y persistencia.
  - Estado: implementado y verificado / pendiente de revision del usuario y
    consolidacion en commit.
  - Evidencia:
    `docs/audits/architecture-first/2026-06-02-6c1-automation-mvc-refactor.md`.

- [x] **Task 6C.2: Refactor MVC de Documents**
  - Aplicar TDD y separar web, API, preview/export, servicios y persistencia.
  - Estado: implementado y verificado / pendiente de revision del usuario y
    consolidacion en commit.
  - Evidencia:
    `docs/audits/architecture-first/2026-06-02-6c2-documents-mvc-refactor.md`.

- [x] **Task 6C.3: Planificar lotes restantes segun evidencia 6A**
  - Priorizar superficies por riesgo real antes de tocar codigo.
  - Estado: plan residual documentado / pendiente de revision del usuario y
    consolidacion en commit.
  - Evidencia:
    `docs/audits/architecture-first/2026-06-02-6c3-residual-batch-plan.md`.

- [x] **Task 6C.4: Refactor MVC y Requests de Roles**
  - Extraer construccion UI y centralizar validacion de enrollment, bulk delete
    y sincronizacion de permisos sin redisenar persistencia RBAC.
  - Estado: implementado y verificado / pendiente de revision del usuario y
    consolidacion en commit.

- [x] **Task 6C.5: Refactor MVC y Requests de Media**
  - Extraer construccion UI y centralizar bulk mutations preservando storage,
    metadata dinamica y claims.
  - Estado: implementado y verificado / pendiente de revision del usuario y
    consolidacion en commit.

- [x] **Task 6C.6: Centralizar Requests de Operations**
  - Mover validacion y normalizacion de mutaciones Operations fuera de
    controladores, preservando rutas y throttles.
  - Estado: implementado y verificado / pendiente de revision del usuario y
    consolidacion en commit.

- [x] **Task 6C.7: Normalizar contrato i18n de manifests**
  - Retirar correcciones posicionales fragiles para manifests activos y
    normalizar strings visibles App; mantener DevTools diferido.
  - Estado: consolidado en `00487f6`.
  - Evidencia:
    `docs/audits/architecture-first/2026-06-02-6c4-6c7-runtime-normalization.md`.

- [x] **Task 6D.1: Cerrar contrato documental**
  - Definir indice hot/warm/cold, inventario verificable e inline docs por
    frontera de riesgo sin convertir `STRUCTURE.md` en catalogo exhaustivo.
  - Estado: implementado y verificado.
  - Evidencia:
    `docs/audits/architecture-first/2026-06-02-6d1-documentation-contract.md`.

- [x] **Task 6D.2: Documentar contratos JS canonicos**
  - Cubrir inicializacion, DOM, eventos, payloads y supuestos CSP de scripts
    no triviales sin narracion redundante.
  - Estado: implementado y verificado.
  - Evidencia:
    `docs/audits/architecture-first/2026-06-02-6d2-js-contracts.md`.

- [ ] **Task 6D.3: Planificar migracion de templates PHP ejecutables**
  - Inventariar y migrar por superficies acotadas; mantener fallback PHP hasta
    alcanzar inventario cero con regresiones.

- [ ] **Task 6D.4: Planificar extraccion de inline JS/CSS**
  - Separar comportamiento hacia assets versionados conservando CSP y bloques
    JSON de transporte intencionales.

- [ ] **Task 6E.1: Verificar y presentar cierre de fase**
  - Ejecutar Composer, quality gate, inspectores, sync documental y diff checks.
  - No marcar fase 6 completada sin confirmacion explicita del usuario.
