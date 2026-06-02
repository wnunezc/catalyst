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

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Argument\Option;
use Catalyst\Framework\Cli\AbstractCommand;
use Catalyst\Framework\Geo\GeoManager;
use Throwable;

/**
 * Defines the Geo Smoke Command class contract.
 *
 * @package Catalyst\Framework\Cli\Commands
 * Responsibility: Coordinates the geo smoke command behavior within its module boundary.
 */
final class GeoSmokeCommand extends AbstractCommand
{
    /** @return Option[] */
    public function getOptions(): array
    {
        return [
            new Option(null, 'json', false, false, 'Render as JSON', false),
        ];
    }

    /**
     * Returns the name value.
     */
    public function getName(): string
    {
        return 'geo:smoke';
    }

    /**
     * Returns the description value.
     */
    public function getDescription(): string
    {
        return 'Exercise canonical PA-07 geo normalization, distance and bounding-box semantics';
    }

    /**
     * Executes the service workflow.
     */
    public function execute(ArgumentBag $args): int
    {
        $json = (bool) ($args->getOptionValue('json') ?? false);
        $geo = GeoManager::getInstance();
        $result = [
            'steps' => [],
            'success' => false,
        ];

        try {
            $origin = $geo->coordinate(0, 0);
            $eastOneDegree = $geo->coordinate(0, 1);
            $wrapped = $geo->coordinate(8.9823792, 181);
            $distanceKm = $geo->distanceKilometers($origin, $eastOneDegree, 3);
            $boundingBox = $geo->boundingBox($origin, 1000);
            $inside = $boundingBox->contains($geo->coordinate(0.005, 0.005));
            $outside = $boundingBox->contains($geo->coordinate(0.05, 0.05));

            $result['steps'][] = [
                'step' => 'longitude-normalization',
                'status' => abs($wrapped->longitude() + 179.0) < 0.000001 ? 'ok' : 'failed',
                'longitude' => $wrapped->longitude(),
            ];
            $result['steps'][] = [
                'step' => 'distance-equator-1deg',
                'status' => abs($distanceKm - 111.195) <= 0.05 ? 'ok' : 'failed',
                'distance_km' => $distanceKm,
            ];
            $result['steps'][] = [
                'step' => 'bounding-box-contains-inner',
                'status' => $inside ? 'ok' : 'failed',
            ];
            $result['steps'][] = [
                'step' => 'bounding-box-rejects-outer',
                'status' => !$outside ? 'ok' : 'failed',
            ];

            foreach ($result['steps'] as $step) {
                if (($step['status'] ?? 'failed') !== 'ok') {
                    throw new \RuntimeException('Geo smoke assertion failed at step: ' . ($step['step'] ?? 'unknown'));
                }
            }

            $result['distance_km'] = $distanceKm;
            $result['bounding_box'] = $boundingBox->toArray();
            $result['success'] = true;
        } catch (Throwable $e) {
            $result['error'] = $e->getMessage();
        }

        if ($json) {
            $this->line((string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return !empty($result['success']) ? 0 : 1;
        }

        $this->line('');
        $this->info('Geo Smoke');
        $this->line('');

        foreach ((array) ($result['steps'] ?? []) as $step) {
            $this->line(sprintf(
                '  %-28s %-8s',
                (string) ($step['step'] ?? 'step'),
                strtoupper((string) ($step['status'] ?? 'unknown'))
            ));
        }

        $this->line('');

        if (!empty($result['success'])) {
            $this->success('Geo smoke passed.');

            return 0;
        }

        $this->error((string) ($result['error'] ?? 'Geo smoke failed.'));

        return 1;
    }
}
