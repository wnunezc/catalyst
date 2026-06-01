# Refactor visual v2 del framework Catalyst

- Fecha: 2026-05-24
- Estado: documentado sobre runtime local actualizado; sin reabrir implementacion visual
- Objetivo: registrar con precision tecnica el estado alcanzado por el parche visual v2 y diferenciarlo del enfoque del parche v1

## Contexto

Catalyst mantiene una UI administrativa propia inspirada en INSPINIA, integrada sobre un runtime MVC modular con CSP endurecida y carga clasica request-response. El trabajo visual documentado en esta fase no introduce un rediseño nuevo ni una copia del vendor, sino una correccion incremental del shell administrativo, auth, DataGrid y superficies modulares ya existentes.

La referencia visual local mas cercana al tema original se encuentra en:

- Canonica: `D:/OpsZone/DevWorkspace/Projects/Web/theme/WB0R5L90S/INSPINIA_v5.0/Bootstrap/HTML/Admin/src`
- Auxiliar para mapping PHP, solo si hace falta: `D:/OpsZone/DevWorkspace/Projects/Web/theme/WB0R5L90S/INSPINIA_v5.0/Bootstrap/PHP/inspinia/src`

## Resumen ejecutivo

El parche visual v1 avanzo la compactacion general del framework, pero parte de ese resultado se apoyo en una reduccion tipografica excesiva. El parche visual v2 corrige ese enfoque: restaura una escala de texto legible y desplaza la compactacion hacia espaciados, paddings, margins, gaps, alturas de controles, densidad de cards, densidad de toolbar y estructura del shell.

El resultado actual se apoya en cuatro frentes:

1. Shell administrativo mas compacto y estable, con sidebar de altura completa, scroll interno real y agrupacion visual por secciones.
2. Auth y branding institucional protegidos contra solapamientos de logos y textos largos.
3. DataGrid con toolbar superior e inferior, dropdown `Tools`, paginacion ampliada y controles compatibles con CSP.
4. Ajustes por modulo para que las superficies administrativas y de workspace sigan el mismo ritmo visual sin recurrir a texto demasiado pequeno.

## Diferencia entre parche visual v1 y v2

| Area | Parche v1 | Parche v2 |
|---|---|---|
| Enfoque general | Compactacion inicial del shell y superficies | Correccion del enfoque para mantener texto legible y compactar por estructura |
| Tipografia | Reduccion visible en varias vistas | Escala legible restaurada; la compactacion pasa a spacing y chrome |
| Sidebar | Menor densidad base, pero sin documentacion de agrupacion completa | Scroll real, grupos visuales, submenu de Operaciones y mejor jerarquia |
| Auth | Compactado inicial | Correccion de espaciado y proteccion de marcas institucionales |
| DataGrid | Toolbar compacta y exportaciones visibles | Toolbar arriba y abajo, `Tools`, `per_page`, `First/Last`, sin inline JS |
| Branding | Integracion inicial | Logos compactos seguros, nombres largos acotados y tagline protegido |

## Justificacion del cambio de enfoque

La reevaluacion del resultado visual llevo a una conclusion concreta: el problema principal no era el tamano del texto, sino el exceso de espacio entre elementos y la falta de densidad consistente entre shell, cards, toolbars, tablas y formularios.

En el estado documentado:

- `public/assets/css/catalyst/admin-layout.css` contiene una pasada de compactacion y una segunda pasada identificada como `density correction v2`.
- `public/assets/css/catalyst/auth.css` replica el mismo patron: compactacion inicial y luego correccion para mantener una escala de lectura normal.
- Los estilos de modulo bajo `Repository/.../front/style.css` y sus copias publicadas en `public/assets/css/work/...` muestran el mismo criterio: se restauran titulos, eyebrow y base font, mientras se reduce densidad vertical por espaciado.

La decision tecnica fue compactar la interfaz sin comprimir el texto por debajo de una lectura administrativa razonable.

## Cambios por area

### Layout administrativo

El shell administrativo consolidado en `public/assets/css/catalyst/admin-layout.css`, `boot-core/template/layouts/admin.phtml` y `boot-core/template/scope/layouts/admin.php` reorganiza el layout sobre un sidebar fijo con scroll interno, topbar compacta y contenido central con cards y toolbars de menor altura.

Cambios observables:

- lectura base restaurada alrededor de `0.875rem` en el shell;
- reduccion de paddings, gaps y alturas de controles;
- estabilizacion del ancho lateral y del espacio util del contenido;
- agrupacion del menu lateral por bloques semanticos;
- continuidad visual entre admin y workspace.

### Sidebar / sidenav

El template `boot-core/template/components/_admin-shell-sidenav.phtml` y su scope asociado reciben una navegacion agrupada por contexto. `boot-core/template/scope/layouts/admin.php` calcula grupos activos y anida entradas hijas para administracion.

Cambios efectivos:

- scroll interno real dentro del sidebar;
- grupos con etiqueta visual (`side-nav-group`, `side-nav-group__label`);
- submenu visible de Operaciones definido desde `Repository/Framework/Operations/module.php`;
- bloqueo de desbordes en lockup institucional para evitar colisiones entre logo, nombre y tagline.

### Topbar

`boot-core/template/components/_admin-shell-topbar.phtml` conserva el rol funcional del topbar, pero se alinea con la compactacion del shell:

- marca compacta y contextual;
- selector de contexto y acciones de usuario sin inflar altura;
- logout por `POST` con formulario real;
- sin inline JS.

### Auth / login

`public/assets/css/catalyst/auth.css` y `boot-core/template/layouts/auth.phtml` reflejan la correccion v2:

- se conserva la compactacion por padding, espacios y alturas;
- se restaura una escala legible para headings, labels y texto auxiliar;
- se protege el lockup institucional contra solapamientos y wraps agresivos;
- se mantiene coherencia con los temas institucionales compartidos por admin y publico.

### DataGrid / tablas

El template `boot-core/template/components/_admin-datagrid.phtml`, su scope `_admin-datagrid.php` y `public/assets/js/catalyst/modules/admin-grid.js` consolidan una tabla administrativa compacta sin sacrificar funcionalidad.

Estado actual documentado:

- header del grid reservado para identidad del bloque;
- toolbar superior entre filtros y tabla;
- footer inferior con el mismo set de controles;
- dropdown `Tools` para export y print;
- selector `per_page` arriba y abajo;
- paginacion con `First`, `Previous`, paginas numeradas, `Next` y `Last`;
- accion `print` ejecutada desde JS externo;
- `per_page` controlado desde JS externo, sin `onchange` inline.

### Cards, botones, badges y formularios

Los estilos de shell y modulo reducen densidad vertical con menos padding interno y menos separacion entre bloques. El criterio no fue eliminar componentes ni mutar el sistema visual hacia otro framework, sino recuperar una lectura mas cercana al ritmo administrativo del tema de referencia.

El efecto es visible en:

- toolbars mas compactas;
- headers de pagina menos altos;
- cards con menor padding y menor distancia entre secciones;
- badges y acciones alineadas con una escala de texto legible.

### Temas institucionales

`app/Framework/Appearance/PlatformAppearanceManager.php` centraliza la politica de apariencia, los presets cerrados del Admin Customizer y el branding neutral. Los skins visuales se aplican mediante `response-skins.css` y `inspinia-runtime-compat.css`.

Decisiones observadas en codigo:

- skins cerrados declarados: `red-cross`, `civil-protection`, `firefighters`, `grempa`;
- assets de logo para shell definidos en variantes compactas tipo favicon SVG;
- watermark PDF mantenido por familia en assets separados;
- herencia de variables de color hacia publico, auth y admin.

### Logos institucionales

La correccion v2 no se limito a color. Tambien protegió la composicion de marca:

- el layout usa variantes compactas o icon-safe cuando existen;
- las imagenes de marca se renderizan con `object-fit: contain`;
- los contenedores evitan overflow horizontal;
- los nombres visibles admiten elipsis;
- la tagline larga puede cortar por palabra o envolverse sin romper el layout.

### Textos institucionales largos

`inspinia-runtime-compat.css` y `admin-surfaces.css` agregan reglas para nombres, métricas y controles compactos. Esto reduce el riesgo de superposicion en:

- shell lateral;
- topbar;
- auth;
- navegacion publica.

### Superficies y modulos afectados

Los cambios visuales no se concentran en un solo modulo. La pasada v2 toca el shell comun y varias familias de vistas:

- `Repository/App/Surface/Dashboard`
- `Repository/Framework/Operations`
- `Repository/Framework/ApiPlatform`
- `Repository/Framework/Roles`
- `Repository/Framework/Automation`
- `Repository/Framework/Catalogs`
- `Repository/Framework/Documents`
- `Repository/Framework/Media`
- `Repository/Framework/DevTools`

En el estado local revisado, cada archivo fuente `Repository/.../front/style.css` tiene una copia publicada equivalente bajo `public/assets/css/work/...`.

## Archivos afectados

### Templates y scopes

- `boot-core/template/components/_admin-datagrid.phtml`
- `boot-core/template/components/_admin-shell-sidenav.phtml`
- `boot-core/template/components/_admin-shell-topbar.phtml`
- `boot-core/template/components/_public-navigation.phtml`
- `boot-core/template/layouts/admin.phtml`
- `boot-core/template/layouts/auth.phtml`
- `boot-core/template/layouts/base.phtml`
- `boot-core/template/scope/components/_admin-datagrid.php`
- `boot-core/template/scope/components/_admin-shell-topbar.php`
- `boot-core/template/scope/components/_public-navigation.php`
- `boot-core/template/scope/layouts/admin.php`

### Runtime de apariencia

- `app/Framework/Appearance/PlatformAppearanceManager.php`
- `Repository/Framework/Operations/Views/pages/appearance.phtml`
- `Repository/Framework/Operations/module.php`

### CSS compartido

- `public/assets/css/catalyst/admin-layout.css`
- `public/assets/css/catalyst/auth.css`
- `public/assets/css/catalyst/response-skins.css`

### CSS por superficie o modulo

- `Repository/App/Surface/Dashboard/front/style.css`
- `Repository/Framework/Operations/front/style.css`
- `Repository/Framework/ApiPlatform/front/style.css`
- `Repository/Framework/Roles/front/style.css`
- `Repository/Framework/Automation/front/style.css`
- `Repository/Framework/Catalogs/front/style.css`
- `Repository/Framework/Documents/front/style.css`
- `Repository/Framework/Media/front/style.css`
- `Repository/Framework/DevTools/front/style.css`
- `public/assets/css/work/dashboard/style.css`
- `public/assets/css/work/operations/style.css`
- `public/assets/css/work/apiplatform/style.css`
- `public/assets/css/work/roles/style.css`
- `public/assets/css/work/automation/style.css`
- `public/assets/css/work/catalogs/style.css`
- `public/assets/css/work/documents/style.css`
- `public/assets/css/work/media/style.css`
- `public/assets/css/work/devtools/style.css`

### JS relacionado con CSP y DataGrid

- `public/assets/js/catalyst/modules/admin-grid.js`

## Archivos nuevos

Pendiente de verificacion historica. En el workspace actual no estan disponibles `catalyst-ui-patch.zip` ni `catalyst-ui-patch-v2.zip`, por lo que no es posible afirmar con evidencia local que archivos fueron creados exactamente en v1 o en v2. La documentacion de esta fase describe el estado actual confirmado por codigo y, cuando corresponde, diferencia v1/v2 por comentarios y estructura observables en los assets.

## Consideraciones CSP

Las decisiones visuales se mantuvieron dentro de las restricciones del proyecto:

- `DataGrid` usa listeners externos en `admin-grid.js` para `print` y `per_page`;
- no se documentaron nuevos `onclick`, `onchange` ni `javascript:` en templates runtime revisados;
- el logout del topbar permanece como formulario `POST`;
- la compactacion visual se resolvio por CSS y estructura, no por inline style ad hoc.

## Consideraciones responsive

No se reescribio la UI responsive, pero el estado actual mejora algunos comportamientos practicos:

- sidebar con scroll interno para listas largas;
- logos y tagline con reglas de contencion;
- shell mas compacto sin reducir el texto a tamanos extremos;
- toolbars de DataGrid duplicadas arriba y abajo para reducir desplazamiento en tablas largas.

La validacion responsive profunda en navegador no forma parte de esta pasada documental, salvo las comprobaciones indirectas derivadas del codigo y de las capturas historicas ya mencionadas por el proyecto.

## Validaciones ejecutadas

- `php -v`
- `composer dump-autoload -o`
- `php public/cli.php help`
- `php public/cli.php security:check`
- `php public/cli.php route:lint`
- `php public/cli.php inspect:lint`
- `php public/cli.php route:list`
- lint recursivo PHP sobre `app`, `Repository`, `boot-core` y `public`
- busqueda de inline JS/CSS problematico en `app`, `Repository`, `boot-core` y `public`
- comparacion hash entre `Repository/.../front/style.css` y `public/assets/css/work/...`

## Validaciones pendientes o no ejecutadas

- comparacion historica exacta contra `catalyst-ui-patch.zip` y `catalyst-ui-patch-v2.zip`: no ejecutada porque esos ZIP no estan disponibles en el workspace local
- validacion visual interactiva completa en browser de todas las superficies mencionadas: pendiente de verificacion en esta pasada documental
- atribucion exacta de archivos nuevos creados especificamente en v1 o v2: pendiente de verificacion por falta de artefactos historicos intermedios

## Riesgos conocidos

1. La documentacion puede describir con precision el estado actual, pero no reconstruye al 100% la secuencia exacta de introduccion archivo por archivo sin los ZIP historicos intermedios.
2. La fidelidad a INSPINIA se sostiene como inspiracion visual y referencia local externa; no debe documentarse como copia embebida en Catalyst.
3. La consistencia entre fuentes `Repository/.../front/style.css` y assets publicados en `public/assets/css/work/...` depende de mantener la sincronizacion en futuras iteraciones.
4. El summary del DataGrid sigue en ingles (`Showing X-Y of Z`) en el scope actual; no rompe funcionalidad, pero deja una inconsistencia menor con el resto de la localizacion.

## Recomendaciones para siguientes iteraciones

1. Mantener el criterio de compactacion basado en spacing y estructura, no en reduccion agresiva de fuente.
2. Preservar el sidebar agrupado y con scroll interno como patron base para modulos futuros.
3. Al agregar temas institucionales, definir desde el inicio una variante compacta segura para shell, auth y topbar.
4. Si se publica una nueva ronda visual, versionar explicitamente los ZIP de parche para mejorar trazabilidad documental.
5. Revisar si el summary del DataGrid debe integrarse al sistema de i18n del proyecto.
