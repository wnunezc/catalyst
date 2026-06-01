<?php

declare(strict_types=1);

namespace Catalyst\Framework\Catalog;

use Catalyst\Framework\Temporal\EffectiveWindow;

final class CatalogItemAvailabilityDecorator
{
    private EffectiveWindow $window;

    public function __construct()
    {
        $this->window = EffectiveWindow::getInstance();
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public function decorate(array $row): array
    {
        $row['catalog_key'] = trim(strtolower((string) ($row['catalog_key'] ?? '')));
        $row['item_key'] = trim(strtolower((string) ($row['item_key'] ?? '')));
        $row['is_enabled'] = (bool) ($row['is_enabled'] ?? false);
        $row['sort_order'] = (int) ($row['sort_order'] ?? 0);
        $row['lock_version'] = (int) ($row['lock_version'] ?? 1);
        $row['metadata_json'] = is_array($row['metadata_json'] ?? null)
            ? $row['metadata_json']
            : (json_decode((string) ($row['metadata_json'] ?? '[]'), true) ?: []);

        $decorated = $this->window->decorate([
            'valid_from' => $row['valid_from'] ?? null,
            'valid_to' => $row['valid_to'] ?? null,
        ]);

        $row['temporal_state'] = (string) ($decorated['temporal_state'] ?? EffectiveWindow::STATE_ACTIVE);
        $row['is_available'] = $row['is_enabled'] && $row['temporal_state'] === EffectiveWindow::STATE_ACTIVE;

        return $row;
    }
}
