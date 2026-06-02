<?php

declare(strict_types=1);

namespace Catalyst\Framework\Cli\Commands;

use Catalyst\Framework\Argument\ArgumentBag;
use Catalyst\Framework\Cli\AbstractCommand;

final class MediaMvcRegressionCommand extends AbstractCommand
{
    public function getName(): string
    {
        return 'media:mvc-regression';
    }

    public function getDescription(): string
    {
        return 'Verify Media request and presentation boundaries';
    }

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

    private function contents(string $relativePath): string
    {
        $path = PD . DS . str_replace('/', DS, $relativePath);

        return is_file($path) ? (string) file_get_contents($path) : '';
    }
}
