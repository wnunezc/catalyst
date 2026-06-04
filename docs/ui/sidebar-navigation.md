# Sidebar y navegacion administrativa

## Objetivo

Documentar el criterio actual del sidebar administrativo despues del refactor de navegacion: mas compacto, navegable en listas largas, plegable por areas reales y con mejor separacion entre navegacion principal y secundaria.

## Scroll del sidebar

El sidebar actual usa un contenedor de altura completa con scroll interno real. El comportamiento se apoya en `public/assets/css/catalyst/admin-layout.css`, donde el shell lateral queda fijado a pantalla completa y delega el desplazamiento a su zona de contenido (`.scrollbar`).

Esto resuelve un problema practico del estado previo: menus largos obligaban a perder contexto o a depender del scroll del documento completo.

## Agrupacion visual

La navegacion ya no se presenta como una lista plana uniforme. `boot-core/template/scope/layouts/admin.php` agrupa elementos por contexto y `boot-core/template/components/_admin-shell-sidenav.phtml` los renderiza mediante:

- areas primarias `Workspace`, `Administration` y `DevTools` dentro del sidebar;
- `side-nav-group`
- `side-nav-group__toggle`
- `side-nav-group__panel`
- `side-nav-children`

La agrupacion ayuda a:

- bajar ruido visual;
- reforzar jerarquia;
- aislar subconjuntos funcionales;
- hacer mas compacta la lectura del menu.

## Colapsadores y persistencia

El sidebar ya no deja todos los bloques expandidos al mismo tiempo.

Comportamiento actual:

- cada grupo administrativo (`Acceso y usuarios`, `Contenido y activos`, `Plataforma`, `Framework operations`) es plegable;
- los items con hijas reales, como `Users` u `Operations`, tienen su propio colapsador;
- el grupo o submenu activo abre por defecto;
- los demas grupos arrancan cerrados para evitar una lista vertical interminable;
- `public/assets/js/catalyst/admin-shell.js` persiste el estado en `sessionStorage`;
- el shell versiona tambien `admin-shell.js` desde `boot-core/template/layouts/admin.phtml` para evitar cache viejo contra markup nuevo.

Esto corrige la incoherencia previa donde el menu empezaba agrupado conceptualmente pero terminaba comportandose como una lista estatica sin progresion visual.

## Criterios para anidar opciones

La anidacion es adecuada cuando varias rutas pertenecen al mismo dominio operativo y comparten punto de entrada conceptual. No debe usarse solo por cantidad de items.

Criterios observables en el estado actual:

1. La opcion padre debe representar un dominio reconocible, no una pagina decorativa.
2. Las hijas deben ser operativamente cercanas y previsibles para el usuario administrativo.
3. La anidacion debe reducir ruido de primer nivel, no esconder rutas criticas sin criterio.

## Submenu de Operaciones

`Repository/Framework/Operations/module.php` define `operations.title` dentro del contexto de administracion con entradas hijas para:

- apariencia
- localizacion
- module designer
- feature flags
- plugins
- deployments
- tenancy

Esta decision hace visible que Operaciones es un dominio y no solo una pagina aislada. Tambien evita inflar el primer nivel del sidebar con varias entradas de bajo nivel.

## Limite entre navegacion principal y navegacion secundaria

En la estructura actual:

- el sidebar izquierdo es la superficie primaria para cambiar entre `Workspace`, `Administration` y `DevTools`;
- la navegacion principal debe contener dominios o puntos de entrada de alto nivel;
- la navegacion secundaria debe vivir como hija o grupo contextual cuando depende de un dominio padre;
- acciones de usuario, sesion y contexto no deben migrarse al sidebar si ya viven correctamente en el topbar.

Este limite evita mezclar:

- navegacion de aplicacion;
- configuracion operacional;
- acciones personales del usuario.

Desde `v0.1.0-rc.4`, el sidebar administrativo consume `NavigationRegistry::adminShell()` mediante `AdminShellNavigationPresenter`, pero conserva la taxonomia curada del shell administrativo: `Configuration`, `Workspaces`, `Operations`, `Users` y `Devtools`. Las entradas primarias declaradas por modulos activos en `navigation.admin` son fuente de descubrimiento, no autorizan a reordenar dominios visuales por si solas.

La capa visual conserva el view model historico `demo_ui_nav_groups`, pero este ya no debe mantener listas hardcodeadas para superficies administrativas del framework o de la aplicacion. Los bloques demo de componentes siguen separados y solo aparecen dentro de `/demo-ui`.

Los grupos administrativos deben respetar la taxonomia curada y usar metadata declarativa de modulo (`context`, `group`, `group_label`, `group_order`, `order`, `matches`, `icon`, `visibility`) para descubrir superficies faltantes, permisos, iconos y active state. No se deben duplicar rutas en `_demo-product-shell.php`, pero tampoco se debe permitir que manifests incompletos destruyan la organizacion visual del menu.

Para evitar duplicidad, el contexto activo no se renderiza otra vez como link en el sidebar. La tarjeta de contexto y el titulo del bloque indican el dominio actual; el bloque `Otras áreas` contiene solo saltos a dominios inactivos.

`inspect:lint` ahora valida hijos de navegacion, duplica hrefs por bucket/contexto mediante `navigation-duplicate-href` y falla con `admin-shell-navigation-not-registry-driven` si el shell vuelve a desconectarse de `NavigationRegistry`.

El smoke especifico para este contrato es:

```powershell
php public/cli.php admin-navigation:smoke --json
```

Este smoke comprueba que las entradas `navigation.admin` se proyectan al modelo de sidebar sin romper la taxonomia completa: hrefs canonicos presentes, superficies esperadas por grupo en orden, `/users/organization-hierarchy` y `/admin/account-recovery` bajo `Users`, `Test Features`, `UI Showcase`, `UML / Architecture` y `Demo UI` bajo `Devtools`, sin `Users` anidado, sin `Operations` dentro de `Configuration` y sin `Devtools` duplicado.

El smoke permite entradas adicionales declaradas por modulos de aplicaciones derivadas dentro de grupos canonicos. La regla es preservacion, no igualdad exacta: Catalyst debe conservar sus superficies base en orden y proyectar todos los hrefs declarados, pero una app puede agregar rutas como `/rtm/profile` o `/rtm/radio` bajo `Operations` sin fallar `quality:check`.

## Entradas tecnicas y aliases

Las rutas auxiliares, callbacks, aliases legacy y smoke helpers no deben convertirse en entradas primarias del sidebar. Ejemplos vivos:

- `/users/register` vive como hija de `Usuarios`;
- `/test-layout` se mantiene como ruta DevTools por URL/contexto, sin entrada primaria;
- `/test-features/module-designer*` se mantiene como alias legacy de `Operations`, no como ruta canonica de DevTools.

## Recomendaciones para futuros modulos

1. Agregar nuevas entradas al primer nivel solo si representan un dominio estable y reconocible.
2. Si un modulo trae varias subpantallas cercanas, preferir un padre con hijas antes que varias entradas planas.
3. Mantener labels cortos y operativos para que el sidebar conserve densidad y legibilidad.
4. Verificar siempre el comportamiento con scroll interno cuando la lista crezca.
5. Validar estado activo de grupo e hija cuando se agreguen nuevas rutas.
6. Verificar que markup y JS del sidebar compartan version de assets para no romper plegado por cache.

## Riesgos conocidos

- Si se agregan demasiados grupos o demasiadas hijas por grupo, el beneficio visual puede degradarse y el sidebar volver a sentirse denso.
- La taxonomia del menu depende de criterios del scope; si cambian contextos o patrones de ruta, la agrupacion debe revisarse en conjunto.
- La evidencia historica exacta de cuando aparecio cada grupo en v1 o v2 queda pendiente de verificacion sin los ZIP intermedios.
