# Manifiesto tecnico de archivos del refactor visual v2

| Archivo | Tipo de cambio | Área afectada | Motivo del cambio | Riesgo | Notas |
|---|---|---|---|---|---|
| `boot-core/template/components/_admin-datagrid.phtml` | Ajuste de template | DataGrid | Reubicar controles en toolbar superior e inferior y consolidar `Tools` | Bajo | Sin inline JS; depende del scope y `admin-grid.js` |
| `boot-core/template/components/_admin-shell-sidenav.phtml` | Ajuste de template | Shell admin | Agrupacion visual, lockup institucional y soporte de submenu | Medio | Cambios sensibles a overflow y altura del sidebar |
| `boot-core/template/components/_admin-shell-topbar.phtml` | Ajuste de template | Topbar | Compactar chrome superior sin perder acciones de contexto/usuario | Bajo | Mantiene logout por `POST` |
| `boot-core/template/components/_public-navigation.phtml` | Ajuste de template | Shell publico | Compartir branding institucional con reglas de contencion | Bajo | Depende del payload de appearance |
| `boot-core/template/layouts/admin.phtml` | Ajuste de layout | Shell admin | Cargar shell CSS y assets compartidos | Bajo | No confirma por si solo el detalle visual |
| `boot-core/template/layouts/auth.phtml` | Ajuste de layout | Auth | Incorporar branding institucional y densidad corregida | Bajo | Debe mantenerse alineado con `auth.css` |
| `boot-core/template/layouts/base.phtml` | Ajuste de layout | Publico | Dar salida a navegacion publica con branding compartido | Bajo | Impacto indirecto en landing/publico |
| `boot-core/template/scope/components/_admin-datagrid.php` | Ajuste de scope | DataGrid | Preparar toolbar, summary, `per_page`, paginacion y exports | Medio | Summary visible sigue en ingles |
| `boot-core/template/scope/components/_admin-shell-topbar.php` | Ajuste de scope | Topbar | Resolver datos de marca y usuario para el topbar compacto | Bajo | Vinculado a branding institucional |
| `boot-core/template/scope/components/_public-navigation.php` | Ajuste de scope | Publico | Proveer datos de branding y acciones publicas | Bajo | Reusa runtime de apariencia |
| `boot-core/template/scope/layouts/admin.php` | Ajuste de scope | Navegacion admin | Agrupar navegacion y resolver submenu activo | Medio | Define la estructura semantica del sidebar |
| `app/Framework/Appearance/PlatformAppearanceManager.php` | Ajuste de runtime | Appearance | Centralizar familias institucionales, logos compactos y colores | Medio | Afecta admin, auth, publico y watermark PDF |
| `Repository/Framework/Operations/Views/pages/appearance.phtml` | Ajuste de vista | Appearance UI | Exponer configuracion y preview del theming institucional | Bajo | Ligado a operaciones/appearance |
| `Repository/Framework/Operations/module.php` | Ajuste de modulo | Navegacion admin | Introducir submenu de Operaciones y agrupar acciones | Medio | Impacta discovery en sidebar |
| `Repository/Framework/Operations/front/style.css` | Ajuste CSS v1 + v2 | Operations | Compactar la superficie y luego restaurar lectura legible | Bajo | Tiene comentario de `density correction v2` |
| `Repository/Framework/ApiPlatform/front/style.css` | Ajuste CSS v1 + v2 | API Platform | Reducir densidad vertical sin texto pequeno | Bajo | Sincronizado con asset publicado |
| `Repository/Framework/Roles/front/style.css` | Ajuste CSS v1 + v2 | Roles | Compactar cards/toolbars y corregir titulos | Bajo | Sincronizado con asset publicado |
| `Repository/Framework/Automation/front/style.css` | Ajuste CSS v1 + v2 | Automation | Ajustar densidad de listing y acciones | Bajo | Sincronizado con asset publicado |
| `Repository/Framework/Catalogs/front/style.css` | Ajuste CSS v1 + v2 | Catalogs | Normalizar spacing en cards y formularios | Bajo | Sincronizado con asset publicado |
| `Repository/Framework/Documents/front/style.css` | Ajuste CSS v1 + v2 | Documents | Corregir densidad visual y ritmo vertical | Bajo | Sincronizado con asset publicado |
| `Repository/Framework/Media/front/style.css` | Ajuste CSS v1 + v2 | Media | Reducir padding/gaps y conservar lectura | Bajo | Sincronizado con asset publicado |
| `Repository/Framework/DevTools/front/style.css` | Ajuste CSS v1 + v2 | DevTools | Compactar una superficie amplia manteniendo jerarquia | Medio | Archivo grande; conviene tocarlo con precision |
| `Repository/App/Surface/Dashboard/front/style.css` | Ajuste CSS v1 + v2 | Dashboard | Corregir titulos y spacing del dashboard | Bajo | Sincronizado con asset publicado |
| `public/assets/css/catalyst/admin-layout.css` | Ajuste CSS compartido | Shell admin | Compactacion base y `density correction v2` del shell | Medio | Archivo central del look administrativo |
| `public/assets/css/catalyst/auth.css` | Ajuste CSS compartido | Auth | Compactacion del login y correccion de escala tipografica | Medio | Afecta branding institucional del acceso |
| `public/assets/css/catalyst/response-skins.css` | Ajuste CSS compartido | UI skins | Presets Red Cross, Civil Protection, Firefighters y GREMPA con contraste cerrado | Medio | Cambios transversales al shell admin/demo-ui |
| `public/assets/js/catalyst/modules/admin-grid.js` | Ajuste JS externo | DataGrid / CSP | Mover `print` y `per_page` a listeners externos | Bajo | Clave para no introducir JS inline |
| `public/assets/css/work/dashboard/style.css` | Publicacion de asset | Dashboard | Reflejar el CSS fuente de `Repository` | Bajo | Hash local coincide con el fuente |
| `public/assets/css/work/operations/style.css` | Publicacion de asset | Operations | Reflejar el CSS fuente de `Repository` | Bajo | Hash local coincide con el fuente |
| `public/assets/css/work/apiplatform/style.css` | Publicacion de asset | API Platform | Reflejar el CSS fuente de `Repository` | Bajo | Hash local coincide con el fuente |
| `public/assets/css/work/roles/style.css` | Publicacion de asset | Roles | Reflejar el CSS fuente de `Repository` | Bajo | Hash local coincide con el fuente |
| `public/assets/css/work/automation/style.css` | Publicacion de asset | Automation | Reflejar el CSS fuente de `Repository` | Bajo | Hash local coincide con el fuente |
| `public/assets/css/work/catalogs/style.css` | Publicacion de asset | Catalogs | Reflejar el CSS fuente de `Repository` | Bajo | Hash local coincide con el fuente |
| `public/assets/css/work/documents/style.css` | Publicacion de asset | Documents | Reflejar el CSS fuente de `Repository` | Bajo | Hash local coincide con el fuente |
| `public/assets/css/work/media/style.css` | Publicacion de asset | Media | Reflejar el CSS fuente de `Repository` | Bajo | Hash local coincide con el fuente |
| `public/assets/css/work/devtools/style.css` | Publicacion de asset | DevTools | Reflejar el CSS fuente de `Repository` | Bajo | Hash local coincide con el fuente |
| `boot-core/requirement-loader/spl-autoload.php` | Revision sin delta visual confirmado | Bootstrap | Archivo solicitado para revision documental | Nulo | No se confirmo impacto visual directo en esta pasada |

## Nota sobre archivos nuevos

La disponibilidad local de `catalyst.zip` y `src.zip` del theme no permite reconstruir con precision que archivos nacieron especificamente en v1 o v2. La ausencia local de `catalyst-ui-patch.zip` y `catalyst-ui-patch-v2.zip` deja esa atribucion como pendiente de verificacion.
