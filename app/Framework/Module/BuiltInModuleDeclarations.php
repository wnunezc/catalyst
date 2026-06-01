<?php

declare(strict_types=1);

namespace Catalyst\Framework\Module;

final class BuiltInModuleDeclarations
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return ModuleRegistry::builtInDeclarations();
    }
}
