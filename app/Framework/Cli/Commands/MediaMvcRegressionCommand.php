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
use Catalyst\Framework\Cli\AbstractCommand;

/**
 * media:mvc-regression CLI command.
 *
 * Responsibility: Runs the media:mvc-regression command to Verify Media request and presentation boundaries.
 *
 * @package Catalyst\Framework\Cli\Commands
 */
final class MediaMvcRegressionCommand extends AbstractCommand
{
    /**
     * Returns the command name registered in the CLI registry.
     *
     * Responsibility: Returns the command name registered in the CLI registry.
     */
    public function getName(): string
    {
        return 'media:mvc-regression';
    }

    /**
     * Returns the short help text shown for this command.
     *
     * Responsibility: Returns the short help text shown for this command.
     */
    public function getDescription(): string
    {
        return 'Verify Media request and presentation boundaries';
    }

    /**
     * Runs the command workflow using parsed CLI arguments.
     *
     * Responsibility: Runs the command workflow using parsed CLI arguments.
     */
    public function execute(ArgumentBag $args): int
    {
        $library = $this->contents('Repository/Framework/Media/Controllers/MediaLibraryController.php');
        $fields = $this->contents('Repository/Framework/Media/Controllers/MetadataFieldController.php');
        $checks = [
            'bulk_request_centralized' => class_exists(\Catalyst\Repository\Media\Requests\MediaBulkSelectionRequest::class)
                && str_contains($library, 'new MediaBulkSelectionRequest($request)'),
            'library_form_extracted' => class_exists(\Catalyst\Repository\Media\Support\MediaLibraryFormFactory::class)
                && !str_contains($library, 'FormBuilder::'),
            'metadata_field_form_extracted' => class_exists(\Catalyst\Repository\Media\Support\MetadataFieldFormFactory::class)
                && !str_contains($fields, 'FormBuilder::'),
        ];
        $ok = !in_array(false, $checks, true);

        $this->line('');
        $this->info('Media MVC Regression');
        $this->line(str_repeat('-', 74));
        foreach ($checks as $name => $passed) {
            $this->line(sprintf('  %-40s %s', ucwords(str_replace('_', ' ', $name)), $passed ? 'OK' : 'ISSUES'));
        }
        $this->line(str_repeat('-', 74));
        $ok ? $this->success('Media MVC contract is coherent.') : $this->error('Media MVC contract has issues.');
        $this->line('');

        return $ok ? 0 : 1;
    }

    /**
     * Describes the contents helper responsibility inside the CLI component.
     *
     * Responsibility: Supports the contents helper workflow used by this CLI component.
     */
    private function contents(string $relativePath): string
    {
        $path = PD . DS . str_replace('/', DS, $relativePath);

        return is_file($path) ? (string) file_get_contents($path) : '';
    }
}
