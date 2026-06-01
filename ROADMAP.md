# Roadmap de Ajuste Visual y Renderizado

Fecha: `2026-05-21`
Proyecto: `D:/OpsZone/DevWorkspace/Projects/Web/catalyst`
Runtime auditado: `https://catalyst.dock/`
Referencia visual objetivo: `D:/OpsZone/DevWorkspace/Projects/Web/theme/WB0R5L90S/INSPINIA_v5.0/Bootstrap`

## Alcance de la auditoria

Se auditó el resultado visible en navegador, no solo el HTML fuente.

- Inventario auditado: `99` entradas HTML del runtime.
- Patrones unicos reales: `94`.
- Capturas reales obtenidas: `93` entradas.
- Rutas bloqueadas hoy: `6`.
- Alias declarados en inventario: `/Dashboard`, `/Home`, `/Landing`, `/Setup`, `/Store`.

Evidencia generada:

- `visual-audit/screenshots/`
- `visual-audit/route-coverage.csv`
- `visual-audit/route-coverage.json`
- `visual-audit/audit-auth-static.json`
- `visual-audit/audit-auth-dynamic.json`
- `visual-audit/audit-guest-static.json`
- `visual-audit/audit-guest-dynamic.json`
- `visual-audit/audit-mfa-challenge.json`

## Hallazgos transversales

### 1. La identidad institucional no controla el layout compartido

La paleta y la identidad visual aparecen sobre todo dentro del contenido. No dominan el shell compartido:

- `topbar`
- `sidebar`
- `footer`
- fondos globales
- estados activos
- badges
- formularios
- tablas

Resultado: la aplicacion se sigue sintiendo como un shell oscuro generico con contenido tematizado encima, no como una experiencia institucional coherente.

### 2. Los logos institucionales estan mal integrados

En varias superficies el logo aparece:

- recortado
- duplicado visualmente
- con caja demasiado pequena
- sin jerarquia tipografica limpia junto al nombre institucional

Esto es visible en admin, publico y auth.

### 3. Hay un problema real de renderizado en navegador

Durante la auditoria se repitio este error en varias superficies:

`SyntaxError: The requested module '../../catalyst/modules/utils.js' does not provide an export named 'renderTextList'`

Impacto observado:

- vistas que tardan en completar el render
- comportamiento inconsistente al entrar por distintas rutas
- degradacion visual aunque el servidor responda bien

Esto afecta especialmente superficies publicas y algunas internas que cargan scripts de `work/*`.

### 4. La entrada publica del sistema no es intuitiva

Comportamiento observado:

- `/` termina en `/login`
- `/Home` termina en `/login`
- `/home` si renderiza la superficie publica esperada
- `/landing` y `/store` renderizan publico

Esto rompe la intuicion de navegacion y complica la evaluacion manual del diseño.

### 5. El tema actual se aleja del objetivo tipo INSPINIA

La composicion visible no se siente alineada al objetivo original por:

- shell demasiado oscuro y plano
- contraste institucional aplicado solo por bloques
- exceso de tarjetas blancas flotando dentro de un marco ajeno
- espaciado y densidad visual inconsistentes entre modulos
- mezcla de estilos entre publico, auth y administracion

### 6. El problema es sistemico, no de una sola vista

Las mismas fallas se repiten en:

- publico: `home`, `landing`, `store`
- auth: `login`, `register`, `forgot-password`, `mfa`
- workspace: `dashboard`, `setup`, `health`
- administracion: `api-platform`, `operations/*`, `catalogs`, `roles`, `users`, `documents`, `media`
- devtools: varias superficies tecnicas

Conclusion: primero hay que corregir el layout compartido y el sistema de tema. Ajustar pantallas una por una antes de eso solo maquillaria el problema.

## Evidencia clave

Capturas representativas:

- Publico `home`: `visual-audit/screenshots/080__guest__home.png`
- Login: `visual-audit/screenshots/089__guest__login.png`
- MFA challenge: `visual-audit/screenshots/093__guest-flow__mfa_challenge.png`
- API Platform: `visual-audit/screenshots/004__auth__api-platform.png`
- Module Designer: `visual-audit/screenshots/048__auth__operations_module-designer.png`
- Setup: `visual-audit/screenshots/062__auth__Setup.png`

En estas capturas se ve con claridad:

- logo recortado
- branding fragmentado
- shell oscuro dominante
- contenido institucional montado encima del shell
- contraste y espaciado inconsistentes

## Rutas no reproducibles hoy

Estas `6` rutas quedaron cubiertas a nivel de inventario, pero no se pudieron renderizar en navegador bajo el estado actual del runtime:

### Dependientes de datos inexistentes

- `/catalogs/{id}`: sin registros actuales en `catalog_definitions`
- `/catalogs/{id}/edit`: sin registros actuales en `catalog_definitions`
- `/catalogs/{id}/items/create`: sin registros actuales en `catalog_definitions`
- `/catalogs/{id}/items/{itemId}/edit`: sin registros actuales en `catalog_items`

### Dependientes de proveedor externo

- `/auth/social/{provider}`
- `/auth/social/callback/{provider}`

Estas requieren un `provider` OAuth real y flujo externo de autenticacion/callback.

## Roadmap priorizado

### Fase 1. Definir el shell institucional compartido

Objetivo: hacer que la identidad institucional controle toda la experiencia, no solo bloques de contenido.

Acciones:

- identificar el layout base real que comparten publico, auth, workspace y administracion
- centralizar tokens de color, fondos, bordes, sombras, tipografia y estados
- aplicar variables del tema a `body`, `sidebar`, `topbar`, `footer`, paneles y overlays
- revisar la jerarquia de color para que el rojo institucional, neutros y acentos funcionen en todo el shell
- evitar que el shell conserve un look oscuro generico mientras el contenido usa otra identidad

Entregable esperado:

- una capa de theming compartida que afecte toda la aplicacion
- paridad visual entre modulo y chrome

### Fase 2. Reparar branding institucional

Objetivo: estabilizar logos, naming institucional y presencia de marca.

Acciones:

- normalizar componente de logo para `public`, `auth`, `workspace`, `admin`
- corregir `width`, `height`, `object-fit`, contenedor, padding y alineacion vertical
- evitar recortes y duplicaciones visuales
- definir variante de logo para sidebar estrecho, topbar y auth card
- revisar si el lockup institucional necesita version horizontal y compacta

Entregable esperado:

- logo consistente y legible en todas las superficies
- encabezados con identidad institucional clara

### Fase 3. Corregir el problema de render diferido o incompleto

Objetivo: quitar la degradacion visible causada por scripts rotos o montaje parcial.

Acciones:

- corregir el contrato del modulo que intenta importar `renderTextList`
- revisar `assets/js/work/home/script.js`
- revisar `assets/js/work/dashboard/script.js`
- identificar si hay mas superficies usando el mismo export roto
- confirmar que el `DOMContentLoaded` y el montaje final no queden esperando codigo invalido
- revalidar tiempos perceptibles de carga en navegador real

Entregable esperado:

- sin errores JS de modulo en consola
- rutas que terminan de renderizar de forma estable y rapida

### Fase 4. Reordenar las superficies publicas y auth

Objetivo: alinear comportamiento de entrada y diseño esperado.

Acciones:

- decidir si la entrada publica primaria debe ser `/` o `/home`
- evitar que `/` y `/Home` manden a login si la intencion de producto es una home publica
- unificar lenguaje visual de `home`, `landing`, `store`, `login`, `register`, `forgot-password`, `reset-password`, `mfa`
- hacer que auth y publico compartan la misma identidad, no solo el mismo logo
- revisar navbar publica, CTA, cards, formularios y fondos

Entregable esperado:

- entry publica coherente
- auth visualmente alineado con la marca institucional

### Fase 5. Ajustar el shell administrativo

Objetivo: hacer que la administracion se sienta institucional sin perder legibilidad operativa.

Acciones:

- rediseñar sidebar, topbar, breadcrumb, paneles y cards sobre la paleta institucional
- revisar contraste real en tablas, formularios, badges y estados activos
- estabilizar espaciado, radios, sombras y densidad visual
- eliminar sensacion de mezcla entre template base oscuro y bloques tematicos superpuestos

Rutas de referencia para esta fase:

- `/api-platform`
- `/operations`
- `/operations/module-designer`
- `/users`
- `/roles`
- `/permissions`
- `/catalogs`
- `/document-templates`
- `/media-library`

Entregable esperado:

- administracion consistente, legible y claramente institucional

### Fase 6. Pulido por familias de superficie

Objetivo: corregir diferencias finas despues de estabilizar el sistema compartido.

Orden recomendado:

1. `public`
2. `auth-flow`
3. `workspace`
4. `administration`
5. `devtools`

En esta fase se corrigen:

- micro-layouts
- encabezados por modulo
- formularios especiales
- tablas complejas
- widgets y paneles secundarios

### Fase 7. Cubrir rutas hoy bloqueadas

Objetivo: completar la auditoria visual con las `6` rutas no reproducibles.

Acciones:

- sembrar al menos un `catalog_definition`
- sembrar al menos un `catalog_item`
- repetir capturas de las 4 rutas dinamicas de catalogos
- ejecutar un flujo social auth real con provider habilitado
- capturar `social start` y `social callback`

Entregable esperado:

- inventario `99/99` con evidencia navegable directa

## Secuencia recomendada de ejecucion

1. Resolver el error JS de `renderTextList`.
2. Definir tokens y shell institucional compartido.
3. Corregir branding y logos.
4. Rehacer publico y auth sobre el shell ya corregido.
5. Ajustar administracion y workspace.
6. Pulir devtools al final.
7. Completar las 6 rutas bloqueadas con datos/proveedor real.

## Criterios de cierre

El ajuste visual debe considerarse cerrado solo cuando se cumpla todo esto:

- la identidad institucional afecta shell y contenido
- no hay logos recortados ni duplicados
- no hay errores JS que degraden el render
- `/` y la ruta publica primaria tienen una intencion clara
- publico, auth, workspace y admin comparten un sistema visual coherente
- las `99` entradas del inventario tienen evidencia navegable satisfactoria

## Nota de enfoque

Los PDF quedaron bien y no son el problema actual. Este roadmap se centra en superficies web renderizadas en navegador.
