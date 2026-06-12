# Sidebar y navegacion administrativa

## Objetivo

Documentar el criterio actual del sidebar administrativo despues del refactor de navegacion: mas compacto, navegable en listas largas, plegable por areas reales y con mejor separacion entre navegacion principal y secundaria.

## Scroll del sidebar

El sidebar actual usa el shell común de Inspinia con un contenedor de altura completa y scroll interno real. El contrato activo se compone desde `boot-core/template/shell.phtml`, `_sidebar.phtml` y `public/assets/css/catalyst/inspinia-runtime-compat.css`.

Esto resuelve un problema practico del estado previo: menus largos obligaban a perder contexto o a depender del scroll del documento completo.

## Agrupacion visual

La navegacion ya no se presenta como una lista plana uniforme.
`DocumentScope` selecciona uno de los tres modelos virtuales mediante
`NavigationModelSelector`: `demo-ui`, `framework-admin` o `application`.
Los proveedores aportan sus árboles a `NavigationTreeNormalizer` y la plantilla
común `boot-core/template/_sidebar.phtml` conserva el renderer único mediante
`_sidebar-node.phtml`.

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
- `public/assets/js/catalyst/shell/navigation.js` controla los colapsadores;
- `ui-runtime.js` carga ese adaptador una sola vez cuando detecta
  `.sidenav-menu`;
- ninguna superficie inicializa un segundo gobernador para el sidebar.

Esto corrige la incoherencia previa donde el menu empezaba agrupado conceptualmente pero terminaba comportandose como una lista estatica sin progresion visual.

## Criterios para anidar opciones

La anidacion es adecuada cuando varias rutas pertenecen al mismo dominio operativo y comparten punto de entrada conceptual. No debe usarse solo por cantidad de items.

Criterios observables en el estado actual:

1. La opcion padre debe representar un dominio reconocible, no una pagina decorativa.
2. Las hijas deben ser operativamente cercanas y previsibles para el usuario administrativo.
3. La anidacion debe reducir ruido de primer nivel, no esconder rutas criticas sin criterio.

## Árbol recursivo y deuda desconectada

El contrato de navegación admite profundidad arbitraria. Los árboles mostrados
en documentación son ejemplos y no límites de profundidad. Cada nodo puede ser
un enlace, un contenedor o un título; el estado activo se propaga hacia sus
ancestros.

La taxonomía administrativa conserva destinos canónicos cuya migración física
está fuera de alcance. Cuando no existe propietario activo, el destino se
renderiza deshabilitado, no clicable y con badge `Disconnected`. El inventario y
backup restaurable de Operations viven en
`docs/architecture/roadmap-2-module-debt.md`.

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

El sidebar común consume `NavigationRegistry::shell()` mediante `ShellNavigationPresenter` y conserva la taxonomía curada: `Configuration`, `Workspaces`, `Operations`, `Users` y `Devtools`. Las entradas declaradas por módulos activos en `navigation.shell` son fuente de descubrimiento y su visibilidad se resuelve exclusivamente con el usuario, sus roles y permisos.

Demo UI obtiene su árbol recursivo de `DemoUiNavigationProvider`; su controlador
solo aporta catálogo y selección. Las demás superficies usan los proveedores
administrativo o Application del documento común, sin perfiles, wrappers,
layouts, shells, temas ni runtimes alternativos.

Los grupos administrativos deben respetar la taxonomia curada y usar metadata declarativa de modulo (`context`, `group`, `group_label`, `group_order`, `order`, `matches`, `icon`, `visibility`) para descubrir superficies faltantes, permisos, iconos y active state. No se deben duplicar rutas en `_demo-product-shell.php`, pero tampoco se debe permitir que manifests incompletos destruyan la organizacion visual del menu.

Para evitar duplicidad, el contexto activo no se renderiza otra vez como link en el sidebar. La tarjeta de contexto y el titulo del bloque indican el dominio actual; el bloque `Otras áreas` contiene solo saltos a dominios inactivos.

`inspect:lint` valida hijos de navegación, hrefs duplicados por bucket/contexto mediante `navigation-duplicate-href` y falla con `shell-navigation-not-registry-driven` si el shell vuelve a desconectarse de `NavigationRegistry`.

El smoke especifico para este contrato es:

```powershell
php public/cli.php shell-navigation:smoke --json
```

Este smoke comprueba que las entradas `navigation.shell` se proyectan al modelo de sidebar sin romper la taxonomía completa: hrefs canónicos presentes, superficies esperadas por grupo en orden, `/users/organization-hierarchy` y `/admin/account-recovery` bajo `Users`, `Test Features`, `UI Showcase`, `UML / Architecture` y `Demo UI` bajo `Devtools`, sin `Users` anidado, sin `Operations` dentro de `Configuration` y sin `Devtools` duplicado.

El smoke permite entradas adicionales declaradas por modulos de aplicaciones derivadas dentro de grupos canonicos. La regla es preservacion, no igualdad exacta: Catalyst debe conservar sus superficies base en orden y proyectar todos los hrefs declarados, pero una app puede agregar rutas como `/rtm/profile` o `/rtm/radio` bajo `Operations` sin fallar `quality:check`.

## Entradas tecnicas y aliases

Las rutas auxiliares, callbacks, aliases legacy y smoke helpers no deben convertirse en entradas primarias del sidebar. Ejemplos vivos:

- `/users/register` vive como hija de `Usuarios`;
- `/test-features/layout-test` se mantiene como diagnóstico DevTools sin entrada primaria;
- `/test-features/*` conserva su contrato existente y no participa en esta
  migración.

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
