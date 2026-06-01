# Business Logic Review

Casos donde la lógica de negocio sí alteraba el riesgo real:

1. Recuperación de contraseña
- Antes del ajuste, cambiar la clave por token no cerraba sesiones persistentes previas.
- La remediación aplicada invalida `remember_tokens` inmediatamente tras `updatePassword()`.

2. Branding y configuración operativa
- `POST /configuration/platform-appearance` persiste contenido que se proyecta en layouts globales.
- El riesgo no estaba en la pantalla en sí, sino en la cadena completa persistencia -> JSON inline -> `<script>`.
- Ese flujo quedó endurecido con flags `JSON_HEX_*`.

3. Setup y bootstrap
- El roadmap debe seguir tratando `/setup/*` como superficie crítica, pero condicionada al estado de instalación.
- La revisión de negocio aquí debe centrarse en evitar que una instancia ya operativa reabra setup o deje workflows de bootstrap accesibles.

4. API tokens y operación automatizada
- La plataforma ya emite y revoca tokens, pero la persistencia actual permite que existan referencias huérfanas.
- El problema es de lifecycle e integridad de negocio, no de autenticación básica del endpoint.

5. DevTools
- Hay endpoints con side effects fuertes como reset de BD o elevación administrativa.
- Conviene reconsiderar el roadmap para tratarlos como chequeo de hardening de despliegue, no como hallazgo base sobre el baseline funcional actual.
