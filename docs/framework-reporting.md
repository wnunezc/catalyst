# Catalyst Framework Reporting

`Catalyst\Framework\Reporting` provides provider-based report definitions and export drivers for framework and app modules. It is framework infrastructure only; apps register domain reports without changing `ReportingManager`.

## Runtime Pieces

- `ReportProviderInterface`: implemented by modules that expose report rows.
- `ReportDefinition`: report key, labels, columns, filters, permissions and enabled formats.
- `ReportProviderRegistry`: deterministic provider lookup with duplicate-key protection.
- `ReportExporterInterface`: converts rows into one concrete output format.
- `DataGridReportExporter`: CSV and Excel-compatible HTML `.xls` exports using the existing DataGrid pipeline.
- `SimplePdfReportExporter`: dependency-free PDF baseline using `SimplePdfWriter`.
- `ReportingManager`: queues persisted report runs, resolves providers, delegates exports and stores generated media.

## Provider Contract

```php
ReportingManager::getInstance()->registerProvider(new TrainingReportProvider());
```

Providers return a definition and rows:

```php
final class TrainingReportProvider implements ReportProviderInterface
{
    public function definition(): ReportDefinition
    {
        return new ReportDefinition(
            key: 'training.by-status',
            label: 'Training by status',
            filename: 'training-by-status',
            resourceKey: 'training-records',
            columns: [
                ['key' => 'code', 'label' => 'Code'],
                ['key' => 'status', 'label' => 'Status'],
            ],
            formats: [ReportFormat::CSV, ReportFormat::XLS, ReportFormat::PDF],
            permissionsAny: ['training.reports.view'],
            filters: [['key' => 'status', 'required' => true]]
        );
    }

    public function rows(array $criteria): array
    {
        return [];
    }
}
```

## Formats

The RC baseline supports:

- `csv`: real CSV through PHP CSV escaping.
- `xls`: Excel-compatible HTML table with `.xls` extension.
- `pdf`: simple generated PDF through the existing framework PDF writer.

Real `xlsx` through `phpoffice/phpspreadsheet` and advanced HTML-to-PDF through `dompdf/dompdf` are intentionally left as optional exporter drivers. Adding those dependencies changes the distributable framework surface and should be approved as a dependency decision before modifying `composer.json`.

## Happy Path

1. Module registers a provider during bootstrap.
2. Consumer queues or resolves a known report key.
3. Criteria are passed to the provider.
4. Provider returns tabular rows.
5. Exporter generates CSV, XLS or PDF contents.
6. `ReportingManager` stores output as generated media and optionally attaches it to a resource.

## Sad Path

The contract rejects unsafe or incoherent report work when:

- report key is unknown;
- provider key is registered twice;
- definition has no key, no label, no filename, no columns or no formats;
- requested format is not supported by the framework baseline;
- definition does not enable the requested format;
- criteria required by a provider are missing.

## Verification

```powershell
php public/cli.php reports:contract-smoke --json
php public/cli.php reporting:smoke --json
```
