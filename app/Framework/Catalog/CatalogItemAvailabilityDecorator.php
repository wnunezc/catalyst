<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * A modern PHP 8.4 framework for building
 * robust and scalable web applications.
 *
 * PHP Version 8.4 (Required).
 *
 * @package    Catalyst
 *
 * @author     Walter Nuñez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nuñez Cruz and Icaros Net
 * @license    Proprietary - https://catalyst.lh-2.net/license
 *
 * @version    GIT: See repository tags
 *
 * @category   Framework
 * @filesource
 *
 * @link       https://catalyst.lh-2.net Project homepage
 * @see        https://catalyst.lh-2.net/docs Documentation
 *
 */

namespace Catalyst\Framework\Catalog;

use Catalyst\Framework\Temporal\EffectiveWindow;

/**
 * Defines the Catalog Item Availability Decorator class contract.
 *
 * @package Catalyst\Framework\Catalog
 * Responsibility: Coordinates the catalog item availability decorator behavior within its module boundary.
 */
final class CatalogItemAvailabilityDecorator
{
    private EffectiveWindow $window;

    /**
     * Initializes the Catalog Item Availability Decorator instance.
     */
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
