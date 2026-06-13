# ROADMAP-5 - Restauracion de propiedad visual Inspinia en superficies de layout comun

Este documento es la fuente canonica de alcance, decisiones arquitectonicas,
ejecucion, seguimiento y criterios de cierre para corregir la arquitectura
CSS/HTML de las superficies Catalyst que cargan el layout comun.

`ROADMAP.md`, `ROADMAP-2.md`, `ROADMAP-3.md` y `ROADMAP-4.md` permanecen como
contratos arquitectonicos anteriores. Este plan los extiende sin reabrir rutas,
propietarios, permisos, grants, navegacion, documento, shell, runtime o
comportamiento funcional ya cerrados.

La premisa central es:

> Inspinia es el propietario de la geometria y del lenguaje visual general.
> Catalyst solamente agrega estructura funcional, adaptadores de runtime,
> compatibilidad CSP, identidad institucional y estilos realmente exclusivos
> de un modulo.

No se creara una nueva capa CSS correctiva para ocultar las colisiones
existentes. La solucion debe retirar o reubicar las reglas desde su causa real.

==================================================
OBJETIVO
==================================================

Restaurar de forma centralizada y verificable el lenguaje visual nativo de
Inspinia dentro del area de contenido de todas las superficies que cargan el
layout comun, preservando:

- el unico documento `boot-core/template/document.phtml`;
- el unico shell `boot-core/template/shell.phtml`;
- el unico contenido comun `boot-core/template/_content.phtml`;
- el unico runtime gobernador
  `public/assets/js/catalyst/runtime/ui-runtime.js`;
- SimpleBar y su propiedad funcional del scroll interno;
- CSP y las sustituciones de estilos inline;
- PageHeader, DataGrid, FormBuilder, RecordPresence y demas capacidades
  centrales;
- temas y skins seleccionables;
- rutas, namespaces, propietarios, permisos, grants y comportamiento;
- las decisiones cerradas por los cuatro roadmaps anteriores.

La implementacion debe determinar para cada regla CSS detectada:

1. si es necesaria;
2. quien es su propietario correcto;
3. si debe conservarse, moverse, reducirse o eliminarse;
4. que consumidores dependen de ella;
5. que evidencia demuestra que no altera funcionalidad.

==================================================
DEFINICION CERRADA DEL ALCANCE
==================================================

## Superficie de layout comun

Una superficie pertenece al alcance cuando su respuesta HTML completa termina
en el contrato:

```text
View::render()
-> DocumentScope::prepare()
-> boot-core/template/document.phtml
-> boot-core/template/shell.phtml
-> boot-core/template/_content.phtml
-> body.catalyst-shell-body
-> div.wrapper
-> main.content-page[data-simplebar]
```

La pertenencia se demuestra desde productor y consumidor. No basta con que un
template contenga una clase visual concreta.

## Incluido

- respuestas HTML completas que usan el layout comun;
- templates de pagina consumidos por esas respuestas;
- PageHeader compartido y sus contratos;
- CSS global cargado por el documento comun;
- CSS fuente y publicado de los modulos propietarios incluidos;
- selectores JavaScript que dependan de clases visuales incluidas;
- pruebas unitarias, smokes y specs Playwright aplicables;
- documentacion de arquitectura CSS y superficies.

## Excluido

- APIs `/api/v1/*`;
- transportes internos `/runtime/*`;
- respuestas JSON, archivos, streams, exports y fragments;
- metodos POST que no renderizan una pagina HTML completa;
- Auth con `catalyst-auth-body` y `auth-layout-shell`;
- superficies Public con `is_public_surface`;
- Account guest con `is_account_guest`;
- Error con `is_error_surface` o fallback HTML de Kernel;
- setup inicial que no consuma el layout comun;
- barras laterales, topbar y status bar, salvo que una regla del area de
  contenido las afecte accidentalmente;
- cambios funcionales de cualquier modulo;
- cambios en rutas, permisos, grants, navegacion o propietarios;
- cambios en dependencias, Composer, package manifests o `vendor`;
- rediseño creativo ajeno a Inspinia.

## Regla evaluar / tocar / preservar

- **Evaluar:** toda superficie que cargue el layout comun.
- **Tocar:** solamente templates o assets con una desalineacion demostrada y
  cuyo cambio sea necesario para restaurar la propiedad visual correcta.
- **Preservar:** superficies conformes, contratos funcionales y superficies
  cerradas que no requieren cambio directo.

Que una superficie este incluida en el inventario no autoriza modificarla.

==================================================
CAUSA RAIZ DEMOSTRADA
==================================================

Actualmente existen propietarios simultaneos y contradictorios del diseño:

1. Inspinia define componentes, geometria y skins.
2. `public/assets/css/catalyst/surfaces.css` vuelve a diseñar PageHeader,
   cards, tablas, botones, badges, listas y densidad general.
3. Los `front/style.css` de modulos vuelven a modificar componentes comunes.
4. Los temas institucionales contienen reglas generales que pueden alterar
   geometria ademas de identidad.
5. La cascada, especificidad y `!important` deciden el resultado final.

Evidencia inicial:

- `surfaces.css` contiene el contrato denominado `Compact executive UI` y una
  `Progressive homogeneity layer`;
- existen reglas descendientes amplias como
  `.surface-page:not(.settings-console) .card-body`;
- PageHeader se representa como `section.page-header.card`;
- los wrappers visuales principales tienen cero consumidores JavaScript;
- la funcionalidad general se monta mediante `data-*`, Bootstrap y el runtime
  central;
- la unica dependencia JavaScript inicial detectada sobre un wrapper visual
  es `.catalogs-page code`;
- `ui-reference.css` contiene sustituciones CSP `mi-inline-*` y adaptadores
  funcionales que no deben confundirse con el sistema visual alternativo.

==================================================
MODELO DE PROPIEDAD CSS OBLIGATORIO
==================================================

| Capa | Propiedad permitida | Propiedad prohibida |
|---|---|---|
| Inspinia vendorizado | Geometria y lenguaje visual general de cards, tablas, formularios, botones, titulos y componentes | Edicion manual de `vendor` o forks locales |
| `surfaces.css` | Estructura funcional reutilizable no cubierta por Inspinia: grid, flex, responsive y estados semanticos | Rediseñar componentes Inspinia, densidad global, temas, skins o modulos |
| `inspinia-runtime-compat.css` | Shell comun, SimpleBar y compatibilidad funcional necesaria para integrar Inspinia al runtime | Segundo tema, estilos de pagina o componentes de negocio |
| `ui-reference.css` | Sustituciones CSP de `style=""` y adaptadores funcionales de plugins reutilizables | Geometria general de superficies o estilos especificos de modulo |
| CSS de capacidades globales | Apariencia y estructura exclusiva de DataGrid, FormBuilder, RecordPresence y capacidades equivalentes | Reglas generales de pagina o modulo |
| `Repository/.../front/style.css` | Comportamiento visual realmente exclusivo del modulo propietario | Recrear shell, layout, PageHeader o componentes Inspinia generales |
| CSS de temas institucionales | Variables, colores e identidad institucional | Cambiar paddings, radios, tamaños, layout o geometria general |
| Utilidades Bootstrap/Inspinia en HTML | Composicion declarativa soportada por el tema | Clases inventadas para sustituir componentes ya disponibles |
| Atributos `data-*` | Contratos de comportamiento JavaScript | Transportar decisiones puramente visuales |

## Prueba obligatoria para conservar una regla CSS

Cada regla Catalyst conservada debe satisfacer al menos una:

- implementa comportamiento funcional que Inspinia no ofrece;
- adapta un plugin externo al tema y al runtime;
- sustituye un estilo inline por requisito CSP;
- implementa layout responsive necesario y no redefine componentes nativos;
- representa identidad institucional exclusivamente mediante color/variables;
- es realmente exclusiva del modulo propietario.

Si ninguna condicion aplica, la regla debe eliminarse o reemplazarse por
composicion nativa de Inspinia/Bootstrap.

==================================================
INVENTARIO INICIAL DE SUPERFICIES
==================================================

El inventario autoritativo debe recalcularse antes de implementar. El baseline
estatico inicial identifica `50` templates candidatos que cargan o son
consumidos por el layout comun.

| Propietario | Templates candidatos | Tratamiento inicial |
|---|---:|---|
| App Dashboard | 1 | Evaluar y tocar solo si demuestra desalineacion |
| Framework Account autenticado | 7 | Evaluar |
| Framework Configuration | 5 | Evaluar; prioridad alta |
| Framework Demo UI | 1 | Referencia y regresion; tocar solo ante bug transversal demostrado |
| Framework DevTools | 4 | Evaluar; prioridad alta |
| Framework Operations | 8 | Evaluar |
| Framework Users | 11 | Evaluar; prioridad alta |
| Framework Workspaces | 13 | Evaluar |
| **Total inicial** | **50** | Recalcular desde productores reales |

## Templates candidatos por propietario

### App Dashboard - 1

- `Repository/App/Surface/Dashboard/Views/pages/index.phtml`

### Framework Account autenticado - 7

- `Repository/Framework/Account/Views/pages/activity.phtml`
- `Repository/Framework/Account/Views/pages/mfa-recovery.phtml`
- `Repository/Framework/Account/Views/pages/mfa.phtml`
- `Repository/Framework/Account/Views/pages/profile.phtml`
- `Repository/Framework/Account/Views/pages/recovery.phtml`
- `Repository/Framework/Account/Views/pages/security.phtml`
- `Repository/Framework/Account/Views/pages/support.phtml`

### Framework Configuration - 5

- `Repository/Framework/Configuration/Views/pages/appearance.phtml`
- `Repository/Framework/Configuration/Views/pages/feature-flags.phtml`
- `Repository/Framework/Configuration/Views/pages/health.phtml`
- `Repository/Framework/Configuration/Views/pages/index.phtml`
- `Repository/Framework/Configuration/Views/pages/plugins.phtml`

### Framework Demo UI - 1

- `Repository/Framework/DemoUi/Views/pages/demo-ui.phtml`

Demo UI permanece funcionalmente cerrado. Se utiliza como referencia de
componentes y regresion del layout comun. No se modifica salvo bug transversal
demostrado y aprobado por los contratos vigentes.

### Framework DevTools - 4

- `Repository/Framework/DevTools/Views/pages/layout-test.phtml`
- `Repository/Framework/DevTools/Views/pages/route-test.phtml`
- `Repository/Framework/DevTools/Views/pages/test-features.phtml`
- `Repository/Framework/DevTools/Views/pages/uml.phtml`

Las rutas `/test-features*` son baseline de regresion. No se modifican
funcionalmente.

### Framework Operations - 8

- `Repository/Framework/Operations/ApiManagement/Views/pages/index.phtml`
- `Repository/Framework/Operations/Audit/Views/pages/index.phtml`
- `Repository/Framework/Operations/Audit/Views/pages/show.phtml`
- `Repository/Framework/Operations/Automation/Views/pages/form.phtml`
- `Repository/Framework/Operations/Automation/Views/pages/index.phtml`
- `Repository/Framework/Operations/Automation/Views/pages/show.phtml`
- `Repository/Framework/Operations/Deployments/Views/pages/deployments.phtml`
- `Repository/Framework/Operations/Tenancy/Views/pages/index.phtml`

### Framework Users - 11

- `Repository/Framework/Users/Views/pages/form.phtml`
- `Repository/Framework/Users/Views/pages/index.phtml`
- `Repository/Framework/Users/Views/pages/organization-hierarchy.phtml`
- `Repository/Framework/Users/Views/pages/permission-form.phtml`
- `Repository/Framework/Users/Views/pages/permissions-list.phtml`
- `Repository/Framework/Users/Views/pages/permissions.phtml`
- `Repository/Framework/Users/Views/pages/recovery-requests.phtml`
- `Repository/Framework/Users/Views/pages/recovery-review.phtml`
- `Repository/Framework/Users/Views/pages/user-register.phtml`
- `Repository/Framework/Users/Views/pages/user-roles.phtml`
- `Repository/Framework/Users/Views/pages/users-index.phtml`

### Framework Workspaces - 13

- `Repository/Framework/Workspaces/Catalogs/Views/pages/form.phtml`
- `Repository/Framework/Workspaces/Catalogs/Views/pages/index.phtml`
- `Repository/Framework/Workspaces/Catalogs/Views/pages/item-form.phtml`
- `Repository/Framework/Workspaces/Catalogs/Views/pages/show.phtml`
- `Repository/Framework/Workspaces/Documents/Views/pages/form.phtml`
- `Repository/Framework/Workspaces/Documents/Views/pages/index.phtml`
- `Repository/Framework/Workspaces/Documents/Views/pages/show.phtml`
- `Repository/Framework/Workspaces/Media/Views/pages/field-form.phtml`
- `Repository/Framework/Workspaces/Media/Views/pages/fields-index.phtml`
- `Repository/Framework/Workspaces/Media/Views/pages/form.phtml`
- `Repository/Framework/Workspaces/Media/Views/pages/index.phtml`
- `Repository/Framework/Workspaces/Views/pages/localization/index.phtml`
- `Repository/Framework/Workspaces/Views/pages/module-designer/index.phtml`

## Inventario de URLs

El inventario definitivo no se construye contando todas las rutas. Debe
resolver exclusivamente las rutas GET/HEAD que producen los templates
anteriores mediante el layout comun.

Para cada URL incluida se debe registrar:

```text
URL
-> handler
-> middleware y permiso
-> productor de scope
-> template
-> body_class
-> shell_class
-> content_class
-> PageHeader
-> CSS global consumido
-> CSS de modulo consumido
-> JavaScript funcional
-> prueba aplicable
-> estado: evaluar / tocar / preservar
```

Debe excluirse cualquier ruta cuyo handler entregue JSON, fragment, descarga,
redirect sin pagina o una capacidad de shell distinta.

==================================================
INVENTARIO INICIAL DE CSS A CLASIFICAR
==================================================

## Global comun

- `public/assets/vendor/inspinia/css/vendors.min.css`
- `public/assets/vendor/inspinia/css/app.min.css`
- `public/assets/css/catalyst/inspinia-runtime-compat.css`
- `public/assets/css/catalyst/datagrid.css`
- `public/assets/css/catalyst/form-builder.css`
- `public/assets/css/catalyst/record-presence.css`
- `public/assets/css/catalyst/surfaces.css`
- `public/assets/css/catalyst/status-bar.css`
- `public/assets/css/catalyst/ui-reference.css`
- `public/assets/css/catalyst/red-cross-theme.css`
- `public/assets/css/catalyst/response-skins.css`

## Fuente de modulos incluidos

- `Repository/Framework/Account/front/style.css`
- `Repository/Framework/Configuration/front/style.css`
- `Repository/Framework/DemoUi/front/style.css`
- `Repository/Framework/DevTools/front/style.css`
- `Repository/Framework/Operations/front/style.css`
- `Repository/Framework/Users/front/style.css`
- `Repository/Framework/Workspaces/front/style.css`
- CSS especifico de submodulos descubierto durante el inventario.
- CSS App cargado por Dashboard, si existe.

## Publicados

Cada asset fuente incluido debe compararse con su copia bajo:

- `public/assets/css/work/{slug}/style.css`;
- `public/assets/js/work/{slug}/script.js`.

Los assets publicados no son una fuente de verdad independiente. Deben quedar
sincronizados con el propietario fuente.

==================================================
CONTRATOS INNEGOCIABLES
==================================================

1. No crear otro documento, shell, layout, perfil, tema o runtime.
2. No crear una capa CSS final de correcciones generales.
3. No editar assets vendorizados de Inspinia.
4. No introducir estilos inline.
5. No eliminar ni degradar clases `mi-inline-*`.
6. No mover sustituciones CSP fuera de `ui-reference.css` sin causa real.
7. Mantener SimpleBar y `[data-simplebar]`.
8. Mantener el scroll interno propiedad de `.content-page`.
9. Mantener el runtime central y los contratos `data-*`.
10. No usar wrappers visuales como contratos JavaScript nuevos.
11. Desacoplar contratos JavaScript existentes de clases puramente visuales.
12. PageHeader permanece como capacidad global unica.
13. PageHeader debe componerse visualmente con Inspinia y no como tema propio.
14. Los temas institucionales solamente gobiernan identidad y color.
15. Los CSS de modulo solamente gobiernan necesidades exclusivas del modulo.
16. No modificar funcionalmente Demo UI ni `/test-features*`.
17. No modificar rutas, namespaces, permisos, grants o navegacion.
18. No alterar payloads, seguridad, CSP ni contratos de error.
19. No ejecutar Playwright; preparar specs y comandos para el usuario.
20. No hacer commit, push, tag o release sin autorizacion explicita.
21. No revertir, sobrescribir ni descartar cambios existentes del usuario.
22. No marcar una fase `Completada` sin confirmacion explicita del usuario.

Estados permitidos:

- `Pendiente`
- `En analisis`
- `Implementada`
- `En revision`
- `Bloqueada`
- `Completada`, solo con confirmacion explicita

==================================================
PROTOCOLO OBLIGATORIO ANTES DE CADA FASE
==================================================

Antes de cada fase:

1. Obtener estado Git fresco y confirmar HEAD.
2. Revisar cambios existentes relacionados sin revertirlos.
3. Analizar productores y consumidores de las superficies afectadas.
4. Confirmar que cada superficie realmente carga el layout comun.
5. Revisar HTML, scope, PageHeader, wrappers, componentes y utilidades.
6. Revisar cascada completa y estilos calculados relevantes.
7. Revisar CSS global, CSS de modulo, tema activo y asset publicado.
8. Revisar consumidores JavaScript y contratos `data-*`.
9. Revisar CSP, SimpleBar y runtime central.
10. Comparar con implementaciones Inspinia vendorizadas aplicables.
11. Identificar Happy Path, Sad Path y regresiones.
12. Clasificar cada regla CSS afectada por propietario correcto.
13. Explicar brevemente que esta incorrecto, que cambiara y que se conserva.
14. Crear o ajustar contratos RED antes de implementar.
15. Ejecutar solamente las pruebas necesarias de la fase.
16. Actualizar la matriz con evidencia fresca.

No detenerse a mitad de una fase.

==================================================
ORDEN OBLIGATORIO DE FASES
==================================================

## FASE 1 - Baseline, backups e inventario autoritativo

Objetivo:

Construir la verdad inicial exacta antes de modificar CSS o HTML.

Acciones:

1. Leer completamente los cinco roadmaps y documentacion UI/CSP aplicable.
2. Obtener estado Git, diff y HEAD reales.
3. Crear backup restaurable completo y backup especifico de templates, CSS,
   JavaScript, assets publicados, pruebas y documentacion incluida.
4. Verificar contenido, restaurabilidad y checksums.
5. Recalcular rutas GET/HEAD que producen HTML con layout comun.
6. Resolver ruta -> handler -> scope -> template -> assets -> prueba.
7. Confirmar o corregir los `50` templates candidatos.
8. Inventariar selectores de cada CSS incluido.
9. Inventariar estilos calculados de componentes representativos:
   PageHeader, card, card-header, card-body, table, button, badge, form,
   breadcrumb, tabs y SimpleBar.
10. Clasificar superficies como evaluar, tocar o preservar.

Criterio de cierre:

- inventario autoritativo de superficies y URLs del layout comun;
- inventario CSS con propietario actual y propietario correcto;
- backups verificados;
- ninguna modificacion funcional.

## FASE 2 - Contratos RED de propiedad CSS

Objetivo:

Codificar las fronteras arquitectonicas antes de retirar reglas.

Contratos requeridos:

- `surfaces.css` no redefine selectores nativos globales de Inspinia;
- temas institucionales no gobiernan geometria general;
- CSS de modulos no recrea shell, layout o PageHeader;
- `ui-reference.css` conserva sustituciones CSP;
- SimpleBar y `data-simplebar` permanecen;
- PageHeader sigue siendo unico;
- clases puramente visuales no son contratos JavaScript;
- source y published assets permanecen sincronizados.

Criterio de cierre:

- contratos fallan exclusivamente por las desalineaciones demostradas.

## FASE 3 - Separar comportamiento JavaScript de clases visuales

Objetivo:

Eliminar dependencias que impidan neutralizar wrappers visuales.

Acciones:

1. Revalidar todos los consumidores JavaScript de wrappers incluidos.
2. Migrar `.catalogs-page code` a contrato semantico `data-*` o utilidad
   declarativa adecuada.
3. Confirmar que SimpleBar, DataGrid, FormBuilder, Bootstrap y runtime usan
   contratos funcionales.
4. No crear bridges permanentes entre selector anterior y nuevo.

Criterio de cierre:

- cero comportamiento depende de wrappers puramente visuales.

## FASE 4 - Restaurar propiedad visual global

Objetivo:

Convertir `surfaces.css` en una capa estructural y funcional neutral.

Acciones:

1. Eliminar `Progressive homogeneity layer`.
2. Retirar reglas descendientes amplias sobre componentes nativos.
3. Retirar densidad, sombras, radios y paddings alternativos generales.
4. Consolidar duplicados y retirar selectores legacy demostrados.
5. Conservar solamente grid, flex, responsive y estados semanticos necesarios.
6. Verificar que Inspinia vuelve a gobernar cards, tablas, botones, badges,
   listas, breadcrumbs y formularios.

Criterio de cierre:

- `surfaces.css` no constituye un segundo sistema visual.

## FASE 5 - Reconciliar PageHeader con Inspinia

Objetivo:

Mantener PageHeader como capacidad global sin representarlo como tarjeta
alternativa.

Acciones:

1. Preservar ViewModel, acciones, metricas, tabs y `data-page-header`.
2. Reemplazar la composicion `section.page-header.card` por composicion
   compatible con `.page-title-head`.
3. Usar geometria, espaciado y responsive nativos de Inspinia.
4. Conservar accesibilidad y contratos de botones.
5. Verificar consumidores de PageHeader del layout comun.

Criterio de cierre:

- PageHeader conserva funcionalidad y adopta lenguaje visual Inspinia.

## FASE 6 - Clasificar y sanear CSS de capacidades globales

Objetivo:

Confirmar que las capacidades globales contienen solamente su responsabilidad.

Alcance:

- `inspinia-runtime-compat.css`;
- `ui-reference.css`;
- `datagrid.css`;
- `form-builder.css`;
- `record-presence.css`;
- `status-bar.css`.

Acciones:

1. Separar compatibilidad funcional de decisiones visuales generales.
2. Mantener SimpleBar y shell sin rediseñar contenido.
3. Mantener adaptadores de plugins y sustituciones CSP.
4. Reubicar reglas mal propietarias solamente cuando exista destino canonico.
5. Eliminar reglas sin consumidor demostrado.

Criterio de cierre:

- cada capacidad global tiene una responsabilidad unica y demostrable.

## FASE 7 - Sanear temas institucionales y skins

Objetivo:

Limitar temas y skins a identidad, color y variables.

Alcance:

- `red-cross-theme.css`;
- `response-skins.css`.

Acciones:

1. Auditar reglas sobre componentes generales.
2. Conservar colores, contrastes e identidad institucional.
3. Retirar cambios de padding, radio, tamaño, layout y geometria general.
4. Verificar que cada skin seleccionable altera identidad sin sustituir
   componentes Inspinia.
5. Verificar Red Cross Fixed en superficies representativas.

Criterio de cierre:

- cambiar tema o skin no cambia la arquitectura del contenido.

## FASE 8 - Sanear CSS de modulos prioritarios

Objetivo:

Retirar sistemas visuales locales de los mayores consumidores.

Orden:

1. DevTools;
2. Users;
3. Configuration.

Acciones por modulo:

1. Clasificar cada selector como funcional, CSP, plugin, estructura exclusiva,
   duplicado global o rediseño general.
2. Conservar solamente necesidades exclusivas.
3. Sustituir componentes recreados por composicion Inspinia/Bootstrap.
4. Sincronizar source y published assets.
5. Verificar todas sus superficies de layout comun.

Criterio de cierre:

- los tres modulos prioritarios consumen Inspinia sin sistema visual local.

## FASE 9 - Sanear CSS de modulos restantes

Objetivo:

Aplicar la misma clasificacion al resto de superficies incluidas.

Orden:

1. Operations;
2. Workspaces;
3. Account autenticado;
4. App Dashboard;
5. Demo UI solamente si existe bug transversal demostrado.

Criterio de cierre:

- todo CSS modular incluido cumple su propiedad correcta.

## FASE 10 - Reconciliar HTML de superficies necesarias

Objetivo:

Eliminar wrappers y markup redundante que ya no tengan responsabilidad.

Acciones:

1. Mantener wrappers semanticos que expresen estructura real.
2. Retirar wrappers exclusivamente visuales sin consumidores.
3. Reemplazar componentes recreados por HTML Inspinia/Bootstrap.
4. No modificar formularios, nombres, acciones, permisos ni comportamiento.
5. No tocar superficies conformes.

Criterio de cierre:

- el HTML expresa estructura y comportamiento, no parches de cascada.

## FASE 11 - Regresion transversal y documentacion

Objetivo:

Demostrar que el cambio central no degrada contratos anteriores.

Acciones:

1. Verificar CSP y ausencia de estilos inline.
2. Verificar SimpleBar y scroll del contenido.
3. Verificar PageHeader, DataGrid, formularios, tabs, modals y acciones.
4. Verificar temas y skins en superficies representativas.
5. Verificar Demo UI y `/test-features*` como baseline sin cambios
   funcionales.
6. Verificar source y published assets.
7. Actualizar documentacion UI para reflejar propiedad CSS real.
8. Actualizar inventario final y matriz.

Criterio de cierre:

- contratos transversales preservados y documentacion alineada.

## FASE 12 - Verificacion final no E2E y preparar Playwright

Objetivo:

Obtener evidencia final necesaria y preparar validacion visual manual.

Acciones:

1. Ejecutar tests unitarios y smokes necesarios.
2. Ejecutar lint, `inspect:lint`, `security:check` y verificaciones CSS/CSP.
3. Ejecutar suite completa solamente al terminar la implementacion si se
   requiere para cierre.
4. Documentar `quality:check` si falla exclusivamente por advisories externos.
5. Preparar specs Playwright pequenas e independientes por familia.
6. Preparar comandos PowerShell de una sola linea.
7. No ejecutar Playwright.
8. Presentar inventario final evaluar/tocar/preservar y evidencia por
   superficie.

Criterio de cierre:

- verificaciones no E2E con evidencia fresca;
- Playwright preparado para ejecucion manual del usuario.

==================================================
MATRIZ INICIAL
==================================================

| Fase | Estado inicial | Evidencia / bloqueo |
|---|---|---|
| 1. Baseline, backups e inventario | Pendiente | Baseline estatico identifica 50 templates candidatos; debe resolverse inventario autoritativo por URL y productor |
| 2. Contratos RED | Pendiente | Existen contratos parciales en `ThemeArchitectureTest`; deben ampliarse sin ocultar fallos reales |
| 3. Desacoplar JavaScript visual | Pendiente | Dependencia inicial demostrada: `.catalogs-page code` |
| 4. Propiedad visual global | Pendiente | `surfaces.css` contiene sistema visual alternativo transversal |
| 5. PageHeader Inspinia | Pendiente | PageHeader actual se representa como `section.page-header.card` |
| 6. Capacidades globales | Pendiente | Requiere clasificacion regla por regla |
| 7. Temas y skins | Pendiente | Requiere separar identidad de geometria |
| 8. Modulos prioritarios | Pendiente | DevTools, Users y Configuration concentran mayor CSS alternativo |
| 9. Modulos restantes | Pendiente | Operations, Workspaces, Account y App Dashboard pendientes de clasificacion |
| 10. HTML necesario | Pendiente | Solo retirar wrappers despues de demostrar ausencia de consumidores |
| 11. Regresion y documentacion | Pendiente | Demo UI y Test Features permanecen baseline funcional |
| 12. Verificacion y Playwright preparado | Pendiente | Playwright no se ejecuta |

==================================================
CRITERIOS DE CIERRE GLOBAL
==================================================

ROADMAP-5 solamente puede cerrarse cuando:

1. Existe inventario final de todas las URLs HTML que cargan el layout comun.
2. Cada URL incluida tiene mapa de productor, template, CSS, JS y prueba.
3. Cada superficie esta clasificada como tocada o preservada con evidencia.
4. Inspinia gobierna geometria y lenguaje visual general.
5. `surfaces.css` no constituye un segundo sistema visual.
6. No existe `Progressive homogeneity layer`.
7. No existen reglas descendientes globales que rediseñen componentes nativos
   dentro de wrappers de superficie.
8. PageHeader permanece unico, funcional y visualmente compuesto con Inspinia.
9. CSS de modulo contiene solo responsabilidad exclusiva del modulo.
10. Temas institucionales gobiernan identidad y color, no geometria general.
11. `ui-reference.css` conserva compatibilidad CSP y clases `mi-inline-*`.
12. Existen cero estilos inline introducidos.
13. SimpleBar y `[data-simplebar]` permanecen funcionales.
14. `.content-page` conserva propiedad del scroll interno.
15. El runtime central permanece unico.
16. Cero comportamientos JavaScript dependen de wrappers puramente visuales.
17. DataGrid, FormBuilder, RecordPresence, Bootstrap y plugins permanecen
   funcionales.
18. Assets fuente y publicados estan sincronizados.
19. Demo UI permanece funcionalmente intacto.
20. `/test-features*` permanece funcionalmente intacto.
21. Auth, Public, Account guest y Error permanecen fuera del cambio visual.
22. No cambian rutas, namespaces, propietarios, permisos, grants o navegacion.
23. No se crea documento, shell, layout, perfil, tema o runtime alternativo.
24. No se crea una nueva capa CSS general de correccion.
25. No se modifican dependencias ni assets vendorizados.
26. Happy Paths y Sad Paths aplicables estan cubiertos.
27. Documentacion describe la propiedad CSS real.
28. Verificaciones no E2E tienen evidencia fresca.
29. Specs Playwright estan preparadas para ejecucion manual.
30. Ninguna fase se marca `Completada` sin confirmacion explicita.
31. No se realiza commit, push, tag o release sin autorizacion explicita.

==================================================
ENTREGA FINAL REQUERIDA
==================================================

La entrega final debe incluir:

1. Estado Git y HEAD reales.
2. Backups, inventarios y checksums.
3. Inventario inicial y final de superficies del layout comun.
4. Matriz URL -> productor -> template -> CSS -> JS -> prueba.
5. Lista exacta de superficies tocadas y preservadas.
6. Matriz regla CSS -> propietario anterior -> decision -> propietario final.
7. Reglas eliminadas, movidas y conservadas con justificacion.
8. Evidencia de propiedad visual Inspinia.
9. Evidencia de PageHeader reconciliado.
10. Evidencia de CSP, `ui-reference.css` y ausencia de inline styles.
11. Evidencia de SimpleBar y runtime central preservados.
12. Evidencia de temas y skins sin geometria alternativa.
13. Evidencia de source/published assets sincronizados.
14. Evidencia de Demo UI y `/test-features*` preservados.
15. Pruebas y verificaciones ejecutadas.
16. Specs Playwright y comandos PowerShell preparados.
17. Riesgos o residuos restantes.
18. Estado final de la matriz.
19. Confirmaciones explicitas pendientes.

==================================================
DECISION FINAL
==================================================

La correccion se ejecutara retirando propiedad visual incorrecta, no
compensandola con mayor especificidad.

Los wrappers estructurales pueden permanecer mientras expresen una
responsabilidad real. Deben ser visualmente neutrales cuando no sean
propietarios del diseño. Los wrappers exclusivamente visuales se retiran solo
despues de demostrar ausencia de consumidores.

Cada CSS detectado debe terminar en el propietario que corresponda a su
responsabilidad:

- Inspinia para componentes y geometria general;
- Catalyst global para capacidades funcionales reutilizables;
- `ui-reference.css` para CSP y adaptadores reutilizables;
- runtime compatibility para shell y SimpleBar;
- tema institucional para identidad y color;
- modulo para comportamiento verdaderamente exclusivo.

Esta distribucion es la condicion necesaria para que todas las superficies del
layout comun respeten el tema seleccionado sin romper JavaScript, CSP,
SimpleBar ni los contratos arquitectonicos cerrados.
