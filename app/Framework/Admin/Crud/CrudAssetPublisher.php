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

namespace Catalyst\Framework\Admin\Crud;

use Catalyst\Framework\Cli\ScaffoldManager;
use RuntimeException;

/**
 * Defines the Crud Asset Publisher class contract.
 *
 * @package Catalyst\Framework\Admin\Crud
 * Responsibility: Coordinates the crud asset publisher behavior within its module boundary.
 */
final class CrudAssetPublisher
{
    /**
     * Initializes the Crud Asset Publisher instance.
     */
    public function __construct(
        private readonly ScaffoldManager $manager
    ) {
    }

    /**
     * @param array<int, array<string, string>> $files
     * @return string[]
     */
    public function publish(array $files, string $slug): array
    {
        $targets = [
            'style.css' => implode(DS, [PD, 'public', 'assets', 'css', 'work', $slug, 'style.css']),
            'script.js' => implode(DS, [PD, 'public', 'assets', 'js', 'work', $slug, 'script.js']),
        ];
        $published = [];

        foreach ($files as $file) {
            $path = (string) ($file['path'] ?? '');
            $contents = (string) ($file['contents'] ?? '');
            $basename = basename($path);

            if (!isset($targets[$basename]) || !str_contains($path, DS . 'front' . DS)) {
                continue;
            }

            $destination = $targets[$basename];
            $this->manager->ensureDirectory(dirname($destination));

            if (file_put_contents($destination, $contents) === false) {
                throw new RuntimeException('Failed to publish generated work asset: ' . $destination);
            }

            $published[] = $destination;
        }

        return $published;
    }
}
