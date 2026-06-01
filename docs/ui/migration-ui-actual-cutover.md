# Migration UI actual cutover

Fecha: 2026-05-27  
Estado: implementado para validación local del usuario

Este parche corrige el problema del cutover anterior: las rutas reales seguían usando el layout visual viejo. Ahora `admin`, `base` y `migration-ui-shell` comparten el mismo shell/runtime INSPINIA usado por `/migration-ui`.

## Decisión aplicada

- Rutas simples públicas preservadas: `/`, `/landing`, `/store`, `/dashboard`.
- Rutas complejas normalizadas según la taxonomía del menú izquierdo:
  - `/configuration/*`
  - `/workspaces/*`
  - `/users/*`
- Rutas legacy mantenidas como compatibilidad temporal.
- `/migration-ui` queda como showcase/staging de componentes, no como destino final del producto.

## Validación esperada

Después de aplicar el ZIP:

```powershell
composer dump-autoload -o; php public/cli.php route:clear; php public/cli.php route:lint; php public/cli.php inspect:lint; php public/cli.php route:list
```

Luego probar rutas finales y verificar que el shell visual corresponda al de INSPINIA `/migration-ui`.
