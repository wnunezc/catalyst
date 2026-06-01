# Lineamientos visuales del DataGrid

## Alcance

Este documento complementa `docs/framework-datagrid.md` y se limita a la capa visual, de integracion de template y de cumplimiento CSP del DataGrid administrativo.

## Principios

El DataGrid actual debe mantenerse compacto sin perder capacidades. La reduccion de densidad se consigue mediante espaciado, altura de controles y orden del toolbar, no eliminando funciones ni reduciendo el texto por debajo de una escala administrativa legible.

## Funcionalidades que no deben eliminarse

1. `Tools` dropdown.
2. Exportaciones soportadas por el grid.
3. Opcion de impresion.
4. Filtros.
5. Busqueda.
6. Bulk actions.
7. Paginacion completa.
8. Selector `per_page`.
9. Acciones por fila.
10. Render de valores estructurados como `stack`, `code` y `badges`.

## Estructura visual recomendada

El template actual `boot-core/template/components/_admin-datagrid.phtml` ya establece la jerarquia recomendada:

1. Header del bloque solo para identidad del grid.
2. Formulario de filtros y busqueda.
3. Toolbar superior con herramientas y paginacion.
4. Tabla.
5. Toolbar inferior con el mismo set de controles.

Este orden evita mezclar identidad, filtros y herramientas dentro de un solo header sobredimensionado.

## Tools dropdown

La agrupacion de `export` y `print` dentro de `Tools` reduce ruido horizontal y mantiene visibles las acciones secundarias sin dispersar botones pequeños por todo el header.

El dropdown no reemplaza funcionalidad; solo concentra:

- export CSV
- export XLS
- print

## Exportaciones

La capa visual del DataGrid no debe asumir que todas las exportaciones son equivalentes. En el runtime documentado:

- CSV es una exportacion real generada por el grid;
- XLS se sirve como tabla HTML con MIME Excel-compatible;
- print no genera endpoint separado; usa `window.print()` desde JS externo.

La UI debe seguir mostrando estas acciones desde `Tools` cuando el grid las habilita.

## Filtros y busqueda

Los filtros y la busqueda deben permanecer visibles antes de la tabla. No deben moverse a menus ocultos si el objetivo es mantener rapidez operativa.

Lineamientos:

- labels claros;
- campos compactos;
- alineacion horizontal cuando el espacio lo permita;
- botones de accion sin inflar la altura del bloque.

## Bulk actions

Las bulk actions deben conservar:

- checkbox maestro;
- checkboxes por fila;
- formulario bulk separado;
- confirmaciones cuando aplique.

La compactacion no debe sacrificar la claridad del estado seleccionado.

## Paginacion y `per_page`

La toolbar superior e inferior deben mantener:

- resumen de rango visible;
- selector `per_page`;
- `First`, `Previous`, paginas, `Next`, `Last`.

Esto reduce scroll innecesario en tablas largas y elimina el patron anterior de controles solo al pie.

## Acciones por fila

Las acciones por fila deben seguir visibles, alineadas con la densidad general de la tabla y sin introducir botones sobredimensionados. Si se agregan acciones nuevas, conviene revisar que:

- no saturen la ultima columna;
- mantengan iconografia y labels coherentes;
- respeten confirmaciones y estados visibles actuales.

## Densidad visual recomendada

La densidad recomendada para grids administrativos en Catalyst es:

- texto base legible;
- paddings de celda moderados;
- toolbar compacta;
- pocas bandas decorativas;
- separacion clara entre filtros, tabla y paginacion;
- acciones secundarias agrupadas.

No se recomienda volver a una estrategia basada en fuente pequena para “ganar espacio”.

## Reglas CSP para JS y CSS del DataGrid

El comportamiento interactivo del grid debe seguir estas reglas:

1. No usar `onclick`, `onchange` ni `javascript:` inline.
2. Resolver `print` desde `public/assets/js/catalyst/modules/admin-grid.js`.
3. Resolver el submit de `per_page` desde JS externo.
4. Activar el comportamiento del grid mediante atributos `data-*`.
5. Mantener estilos en CSS compartido o CSS de modulo; no en `style=""` inline.

## Pendientes y riesgos

- El summary del grid sigue en ingles en el scope actual; si se requiere consistencia completa con i18n, debe revisarse aparte.
- Si una futura iteracion agrega mas acciones a `Tools`, conviene vigilar que el dropdown no se convierta en un contenedor ambiguo de acciones no relacionadas.
- La capa visual no debe desacoplarse del contrato funcional documentado en `docs/framework-datagrid.md`.
