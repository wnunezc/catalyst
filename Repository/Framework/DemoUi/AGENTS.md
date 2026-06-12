# AGENTS.md — Demo UI

## Alcance

- Este archivo aplica a `Repository/Framework/DemoUi/` y a sus recursos acoplados.

## Contrato local

1. La superficie pública final de este módulo es `/demo-ui`.
2. `Demo UI` es una baseline congelada de referencia visual y reusable code.
3. No modificar este módulo salvo:
   - bug crítico comprobado
   - instrucción explícita del usuario
4. No usar `Demo UI` como excusa para reintroducir rutas fallback, aliases legacy ni bundles opacos del tema.
5. Si una mejora pertenece al producto real, implementarla en la superficie canónica del producto y no aquí.
