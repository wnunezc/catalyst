# Executive Decision Notes

Decisiones y criterios surgidos de la auditoría:

1. El riesgo más concreto de la sesión estaba en la transición datos persistidos -> JSON inline -> `<script>`, no en las vistas HTML directas.
2. La continuidad de sesión tras reset de contraseña era un defecto real de negocio y quedó corregido sin cambiar dependencias.
3. El advisory abierto de `symfony/routing` ya requiere decisión explícita de mantenimiento del lock, aunque en esta sesión no se toque `vendor`.
4. La ausencia de FK en `api_tokens.user_id` debe tratarse como deuda de seguridad e integridad, no solo como deuda de esquema.

Reconsideraciones del roadmap:
- SQLi genérico sobre el builder base pierde prioridad frente a integridad de tokens, supply chain y data flows hacia sinks HTML.
- DevTools debe clasificarse como control de despliegue/configuración.
- Setup debe seguir en la lista crítica, pero con la pregunta correcta: "puede reabrirse?" en lugar de "existe la ruta?".

Decisiones pendientes del proyecto:
- Aceptar o no el riesgo temporal del advisory transitivo hasta una ventana de actualización.
- Programar la migración de FK en `api_tokens`.
- Decidir si el framework seguirá permitiendo HTML crudo en respuestas parciales o si migrará a un contrato más estricto.
