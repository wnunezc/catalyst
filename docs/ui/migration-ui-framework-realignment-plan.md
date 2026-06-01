# `/demo-ui` Framework Realignment Plan

Fecha: `2026-05-25`
Estado: `fase 1 ejecutada; fase 2 operativa; fase 3 en endurecimiento`

## Objetivo

`/demo-ui` ya no debe tratarse como una superficie de `App/Surface` ni como un runtime aislado de prueba.

Debe evolucionar hacia dos cosas:

1. un modulo propiedad de `Repository/Framework`
2. un consumidor inicial de un runtime UI nativo de `catalyst.js`, modular y opt-in

## Diagnostico actual

Estado inicial de esta adecuacion:

Ubicacion actual:

- `Repository/App/Surface/DemoUi`

Wiring actual:

- namespace PHP: `App\Surface\DemoUi\...`
- rutas: `Repository/App/Surface/DemoUi/routes.php`
- contrato adicional: `Repository/App/Surface/DemoUi/module.php`
- vistas: `Repository/App/Surface/DemoUi/Views`
- assets fuente: `Repository/App/Surface/DemoUi/front/*`
- previews estaticos: `Repository/App/Surface/DemoUi/generated/theme-previews/*`
- asset runtime publicado: `public/assets/js/work/DemoUi/script.js`

Runtime actual:

- `boot-core/template/components/_catalyst-init.phtml` inicializa `public/assets/js/catalyst/catalyst.js`
- `boot-core/template/layouts/demo-ui-shell.phtml` carga ademas `public/assets/js/work/DemoUi/script.js`
- ese script del surface concentra:
  - sidebar/offcanvas
  - collapses
  - dropdowns
  - code preview/highlight
  - card actions
  - validation
  - topbar state
  - theme manager

Conclusiones:

- `catalyst.js` ya existe, pero hoy no orquesta el shell nuevo
- `/demo-ui` sigue siendo un runtime concentrado en un script de surface
- la ubicacion en `App/Surface` ya no refleja ownership real

## Decision arquitectonica

### Ownership

`/demo-ui` debe pasar a `Repository/Framework`.

Motivo:

- no representa una feature de negocio de la app
- no es una pantalla particular de una surface final
- es la base de migracion del shell/layout que reemplazara UI framework-level

### Runtime JS

Los modulos nuevos no deben llamarse `migration-*`.

Motivo:

- el shell nuevo debe consolidarse como nativo del sistema
- el prefijo `migration` solo describe el estado historico del rollout

El runtime nuevo debe vivir bajo:

- `public/assets/js/catalyst/modules/`

con activacion opt-in desde un entrypoint fino, no con auto-bootstrap global.

## Riesgo principal

El traslado fisico es factible, pero no debe mezclarse con una inicializacion global agresiva del orquestador.

Riesgos concretos:

- romper autoload y namespaces si se mueve carpeta sin cambiar `App\...` a `Catalyst\Repository\...`
- romper carga de vistas si `View::addPath(...)` sigue apuntando a `Repository/App/Surface/DemoUi/Views`
- romper previews si se mantiene hardcodeado el path viejo
- pisar otras vistas si la logica de sidebar/dropdown/theme se mete en `catalyst.js` como auto-init global

## Factibilidad del traslado

Era factible y de complejidad media.

No parece requerir un rediseño total, pero tampoco es un simple rename.

### Lo que juega a favor

- Composer ya tiene el mapeo correcto para framework modules:
  - `Catalyst\\Repository\\` -> `Repository/Framework/`
- `demo-ui-shell` ya vive en `boot-core/template/layouts/`, fuera del modulo
- las rutas publicas pueden mantenerse:
  - `/demo-ui`
  - `/demo-ui/basic-elements`

### Lo que obliga a tocar wiring

- namespace del controlador
- `use` en `routes.php`
- `View::addPath(...)`
- path de `generated/theme-previews`
- cualquier referencia literal a `Repository/App/Surface/DemoUi`

## Plan propuesto

### Fase 1 — traslado canonico del modulo

Estado: `ejecutada`

Mover:

- `Repository/App/Surface/DemoUi`

a:

- `Repository/Framework/DemoUi`

Cambios obligatorios:

- `namespace App\\Surface\\DemoUi\\... (historical)` -> `namespace Catalyst\\Repository\\DemoUi\\... (current)`
- `use App\Surface\DemoUi\Controllers\DemoUiController` -> `use Catalyst\Repository\DemoUi\Controllers\DemoUiController`
- `View::addPath(...)` apuntando a `Repository/Framework/DemoUi/Views`
- `loadThemePreviewHtml()` apuntando a `Repository/Framework/DemoUi/generated/theme-previews`

Compatibilidad esperada:

- las URLs no cambian
- el layout no cambia
- el surface sigue operando igual, pero ya bajo ownership correcto

Resultado ejecutado:

- el modulo ya vive en `Repository/Framework/DemoUi`
- el controlador ya usa `namespace Catalyst\\Repository\\DemoUi\\Controllers (current; historical note previously referenced DemoUi)`
- `routes.php` ya referencia el namespace nuevo
- `View::addPath(...)` ya apunta a `Repository/Framework/DemoUi/Views`
- la carga de previews ya apunta a `Repository/Framework/DemoUi/generated/theme-previews`

### Fase 2 — desacoplar runtime del surface

Estado: `ejecucion inicial completada`

Extraer desde `public/assets/js/work/DemoUi/script.js` hacia modulos nativos bajo `public/assets/js/catalyst/modules/`.

Primer corte sugerido:

- `shell-sidebar.js`
- `shell-dropdown.js`
- `shell-theme-customizer.js`
- `code-preview.js`
- `card-actions.js`

Regla:

- sin prefijo `migration`
- sin auto-init global
- sin asumir que toda vista del sistema usa este shell

Estado actual de la ejecucion:

- `public/assets/js/catalyst/modules/ui-runtime.js` expone `initShellRuntime(...)`
- `public/assets/js/catalyst/catalyst.js` publica ese entrypoint bajo `Catalyst.ui`
- `Repository/Framework/DemoUi/front/script.js` ya no contiene la logica pesada; ahora solo hace bootstrap opt-in del shell
- modulos extraidos en esta pasada:
  - `shell-navigation.js`
  - `shell-dropdowns.js`
  - `shell-theme-customizer.js`
  - `shell-topbar.js`
  - `code-preview.js`
  - `card-actions.js`
  - `form-validation.js`

Capacidades ya incorporadas al runtime:

- inicializadores genericos por deteccion de DOM
- carga opt-in de scripts vendor por necesidad real
- reinicializacion segura cuando un plugin ya existe en el nodo
- soporte actual para:
  - `daterangepicker`
  - `flatpickr`
  - `pickr`
  - `choices`
  - `select2`
  - `wizard`
  - `dropzone`
  - `filepond`
  - `quill`
  - `summernote`
  - `noUiSlider`

Decision adicional cerrada en esta fase:

- los inicializadores viven en runtime global y responden a deteccion de elementos
- el bootstrap del surface no inicializa plugins puntuales
- los CSS vendor no deben reinyectarse dinamicamente si eso rompe el orden de cascade del shell
- las sobreescrituras del tema quedan en `Repository/Framework/DemoUi/front/style.css` y deben cargar despues del CSS base del plugin

Pendiente dentro de la misma fase:

- revisar si alguna parte de `card-actions` y `code-preview` debe renombrar clases residuales `demo-ui-*`
- decidir si el shell nuevo amerita un namespace mas amplio de UI compartida o si `Catalyst.ui.initShellRuntime(...)` basta como contrato canonico

### Fase 3 — entrypoint fino del shell

Dejar `public/assets/js/work/DemoUi/script.js` solo como bootstrap opt-in.

Responsabilidad final deseada:

- detectar si la vista monta el shell nuevo
- invocar modulos concretos del runtime `catalyst`
- no contener logica pesada propia

### Fase 4 — renombre del slug de work assets

Opcional y posterior al traslado estable.

Motivo:

- no mezclar relocation PHP + refactor JS + rename de asset publicado en una sola pasada

Posibles destinos:

- mantener temporalmente `work/DemoUi`
- o migrar luego a un slug mas canonico cuando el shell quede estabilizado

Recomendacion:

- conservar temporalmente `work/DemoUi`
- renombrar despues de cerrar la extraccion modular

## Orden recomendado de ejecucion

1. mover el modulo de `App/Surface` a `Repository/Framework`
2. dejarlo funcionando igual con las mismas rutas
3. extraer modulos JS a `public/assets/js/catalyst/modules/`
4. convertir `work/DemoUi/script.js` en bootstrap minimo
5. renombrar slugs/clases residuales `migration-*` solo cuando el wiring ya este estable
6. al migrar nuevas vistas con plugins, declarar el CSS vendor en la pagina y dejar la inicializacion JS al runtime global por deteccion

## Criterios de aceptacion

El traslado esta bien hecho si:

- `/demo-ui` y `/demo-ui/basic-elements` siguen resolviendo
- no queda namespace `App\\Surface\\DemoUi`
- no queda path runtime apuntando a `Repository/App/Surface/DemoUi`
- el shell nuevo sigue sin contaminar otras vistas del sistema
- `catalyst.js` no autoejecuta comportamientos del shell fuera de vistas opt-in

## Verificacion minima

```powershell
pwsh -Command "composer dump-autoload"
pwsh -Command "php public/cli.php route:lint"
pwsh -Command "php public/cli.php inspect:lint"
```

Verificacion funcional adicional:

- abrir `https://catalyst.dock/demo-ui`
- abrir `https://catalyst.dock/demo-ui/basic-elements`
- confirmar sidebar, dropdown de usuario, theme customizer y code preview
- confirmar en tema oscuro que los plugins renderizan con skin oscura:
  - `pickers`
  - `select`
  - `text-editors`
