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

namespace Catalyst\Repository\DemoUi\Controllers;

use Catalyst\Framework\Auth\AuthManager;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CspNonce;

/**
 * Defines the Demo Ui Controller class contract.
 *
 * @package Catalyst\Repository\DemoUi\Controllers
 * Responsibility: Coordinates the demo ui controller behavior within its module boundary.
 */
final class DemoUiController extends Controller
{
    private const string THEME_BASE_URL = 'https://tema-inspinia.dock/';
    private const string DEFAULT_DOC_FILE = 'ui-alerts.html';
    private const BASE_UI_PAGES = [
        'accordions' => [
            'file' => 'ui-accordions.html',
            'label' => 'Accordions',
            'route' => '/demo-ui/accordions',
            'styles' => [],
        ],
        'alerts' => [
            'file' => 'ui-alerts.html',
            'label' => 'Alerts',
            'route' => '/demo-ui/alerts',
            'styles' => [],
        ],
        'images' => [
            'file' => 'ui-images.html',
            'label' => 'Images',
            'route' => '/demo-ui/images',
            'styles' => [],
        ],
        'badges' => [
            'file' => 'ui-badges.html',
            'label' => 'Badges',
            'route' => '/demo-ui/badges',
            'styles' => [],
        ],
        'breadcrumb' => [
            'file' => 'ui-breadcrumb.html',
            'label' => 'Breadcrumb',
            'route' => '/demo-ui/breadcrumb',
            'styles' => [],
        ],
        'buttons' => [
            'file' => 'ui-buttons.html',
            'label' => 'Buttons',
            'route' => '/demo-ui/buttons',
            'styles' => [],
        ],
        'cards' => [
            'file' => 'ui-cards.html',
            'label' => 'Cards',
            'route' => '/demo-ui/cards',
            'styles' => [],
        ],
        'carousel' => [
            'file' => 'ui-carousel.html',
            'label' => 'Carousel',
            'route' => '/demo-ui/carousel',
            'styles' => [],
        ],
        'collapse' => [
            'file' => 'ui-collapse.html',
            'label' => 'Collapse',
            'route' => '/demo-ui/collapse',
            'styles' => [],
        ],
        'colors' => [
            'file' => 'ui-colors.html',
            'label' => 'Colors',
            'route' => '/demo-ui/colors',
            'styles' => [],
        ],
        'dropdowns' => [
            'file' => 'ui-dropdowns.html',
            'label' => 'Dropdowns',
            'route' => '/demo-ui/dropdowns',
            'styles' => [],
        ],
        'videos' => [
            'file' => 'ui-videos.html',
            'label' => 'Videos',
            'route' => '/demo-ui/videos',
            'styles' => [],
        ],
        'grid-options' => [
            'file' => 'ui-grid.html',
            'label' => 'Grid Options',
            'route' => '/demo-ui/grid-options',
            'styles' => [],
        ],
        'links' => [
            'file' => 'ui-links.html',
            'label' => 'Links',
            'route' => '/demo-ui/links',
            'styles' => [],
        ],
        'list-group' => [
            'file' => 'ui-list-group.html',
            'label' => 'List Group',
            'route' => '/demo-ui/list-group',
            'styles' => [],
        ],
        'modals' => [
            'file' => 'ui-modals.html',
            'label' => 'Modals',
            'route' => '/demo-ui/modals',
            'styles' => [],
        ],
        'notifications' => [
            'file' => 'ui-notifications.html',
            'label' => 'Notifications',
            'route' => '/demo-ui/notifications',
            'styles' => [],
        ],
        'offcanvas' => [
            'file' => 'ui-offcanvas.html',
            'label' => 'Offcanvas',
            'route' => '/demo-ui/offcanvas',
            'styles' => [],
        ],
        'pagination' => [
            'file' => 'ui-pagination.html',
            'label' => 'Pagination',
            'route' => '/demo-ui/pagination',
            'styles' => [],
        ],
        'placeholders' => [
            'file' => 'ui-placeholders.html',
            'label' => 'Placeholders',
            'route' => '/demo-ui/placeholders',
            'styles' => [],
        ],
        'popovers' => [
            'file' => 'ui-popovers.html',
            'label' => 'Popovers',
            'route' => '/demo-ui/popovers',
            'styles' => [],
        ],
        'progress' => [
            'file' => 'ui-progress.html',
            'label' => 'Progress',
            'route' => '/demo-ui/progress',
            'styles' => [],
        ],
        'scrollspy' => [
            'file' => 'ui-scrollspy.html',
            'label' => 'Scrollspy',
            'route' => '/demo-ui/scrollspy',
            'styles' => [],
        ],
        'spinners' => [
            'file' => 'ui-spinners.html',
            'label' => 'Spinners',
            'route' => '/demo-ui/spinners',
            'styles' => [],
        ],
        'tabs' => [
            'file' => 'ui-tabs.html',
            'label' => 'Tabs',
            'route' => '/demo-ui/tabs',
            'styles' => [],
        ],
        'tooltips' => [
            'file' => 'ui-tooltips.html',
            'label' => 'Tooltips',
            'route' => '/demo-ui/tooltips',
            'styles' => [],
        ],
        'typography' => [
            'file' => 'ui-typography.html',
            'label' => 'Typography',
            'route' => '/demo-ui/typography',
            'styles' => [],
        ],
        'utilities' => [
            'file' => 'ui-utilities.html',
            'label' => 'Utilities',
            'route' => '/demo-ui/utilities',
            'styles' => [],
        ],
    ];
    private const FORM_PAGES = [
        'basic-elements' => [
            'file' => 'form-elements.html',
            'label' => 'Basic Elements',
            'route' => '/demo-ui/basic-elements',
            'styles' => [],
        ],
        'pickers' => [
            'file' => 'form-pickers.html',
            'label' => 'Pickers',
            'route' => '/demo-ui/pickers',
            'styles' => [
                '/assets/vendor/inspinia/plugins/daterangepicker/daterangepicker.css',
                '/assets/vendor/inspinia/plugins/flatpickr/flatpickr.min.css',
                '/assets/vendor/inspinia/plugins/pickr/classic.min.css',
                '/assets/vendor/inspinia/plugins/pickr/monolith.min.css',
                '/assets/vendor/inspinia/plugins/pickr/nano.min.css',
            ],
        ],
        'select' => [
            'file' => 'form-select.html',
            'label' => 'Select',
            'route' => '/demo-ui/select',
            'styles' => [
                '/assets/vendor/inspinia/plugins/choices/choices.min.css',
                '/assets/vendor/inspinia/plugins/select2/select2.min.css',
            ],
        ],
        'validation' => [
            'file' => 'form-validation.html',
            'label' => 'Validation',
            'route' => '/demo-ui/validation',
            'styles' => [],
        ],
        'wizard' => [
            'file' => 'form-wizard.html',
            'label' => 'Wizard',
            'route' => '/demo-ui/wizard',
            'styles' => [],
        ],
        'file-uploads' => [
            'file' => 'form-fileuploads.html',
            'label' => 'File Uploads',
            'route' => '/demo-ui/file-uploads',
            'styles' => [
                '/assets/vendor/inspinia/plugins/dropzone/dropzone.css',
                '/assets/vendor/inspinia/plugins/filepond/filepond.min.css',
                '/assets/vendor/inspinia/plugins/filepond/filepond-plugin-image-preview.min.css',
            ],
        ],
        'text-editors' => [
            'file' => 'form-text-editors.html',
            'label' => 'Text Editors',
            'route' => '/demo-ui/text-editors',
            'styles' => [
                '/assets/vendor/inspinia/plugins/quill/quill.core.css',
                '/assets/vendor/inspinia/plugins/quill/quill.snow.css',
                '/assets/vendor/inspinia/plugins/quill/quill.bubble.css',
                '/assets/vendor/inspinia/plugins/summernote/summernote-bs5.min.css',
            ],
        ],
        'range-slider' => [
            'file' => 'form-range-slider.html',
            'label' => 'Range Slider',
            'route' => '/demo-ui/range-slider',
            'styles' => [
                '/assets/vendor/inspinia/plugins/nouislider/nouislider.min.css',
            ],
        ],
    ];
    private const CHART_FAMILIES = [
        'apex' => [
            'label' => 'Apex Charts',
            'slugs' => [
                'charts-apex-area',
                'charts-apex-bar',
                'charts-apex-bubble',
                'charts-apex-candlestick',
                'charts-apex-column',
                'charts-apex-heatmap',
                'charts-apex-line',
                'charts-apex-mixed',
                'charts-apex-timeline',
                'charts-apex-boxplot',
                'charts-apex-treemap',
                'charts-apex-pie',
                'charts-apex-radar',
                'charts-apex-radialbar',
                'charts-apex-scatter',
                'charts-apex-polar-area',
                'charts-apex-sparklines',
                'charts-apex-range',
                'charts-apex-funnel',
                'charts-apex-slope',
            ],
        ],
        'echart' => [
            'label' => 'Echarts',
            'slugs' => [
                'charts-echart-line',
                'charts-echart-bar',
                'charts-echart-pie',
                'charts-echart-scatter',
                'charts-echart-geo-map',
                'charts-echart-gauge',
                'charts-echart-candlestick',
                'charts-echart-area',
                'charts-echart-radar',
                'charts-echart-heatmap',
                'charts-echart-other',
            ],
        ],
    ];
    private const CHART_PAGES = [
        'charts-apex-area' => ['family' => 'apex', 'page' => 'area', 'file' => 'charts-apex-area.html', 'label' => 'Area', 'route' => '/demo-ui/charts/apex/area', 'styles' => []],
        'charts-apex-bar' => ['family' => 'apex', 'page' => 'bar', 'file' => 'charts-apex-bar.html', 'label' => 'Bar', 'route' => '/demo-ui/charts/apex/bar', 'styles' => []],
        'charts-apex-bubble' => ['family' => 'apex', 'page' => 'bubble', 'file' => 'charts-apex-bubble.html', 'label' => 'Bubble', 'route' => '/demo-ui/charts/apex/bubble', 'styles' => []],
        'charts-apex-candlestick' => ['family' => 'apex', 'page' => 'candlestick', 'file' => 'charts-apex-candlestick.html', 'label' => 'Candlestick', 'route' => '/demo-ui/charts/apex/candlestick', 'styles' => []],
        'charts-apex-column' => ['family' => 'apex', 'page' => 'column', 'file' => 'charts-apex-column.html', 'label' => 'Column', 'route' => '/demo-ui/charts/apex/column', 'styles' => []],
        'charts-apex-heatmap' => ['family' => 'apex', 'page' => 'heatmap', 'file' => 'charts-apex-heatmap.html', 'label' => 'Heatmap', 'route' => '/demo-ui/charts/apex/heatmap', 'styles' => []],
        'charts-apex-line' => ['family' => 'apex', 'page' => 'line', 'file' => 'charts-apex-line.html', 'label' => 'Line', 'route' => '/demo-ui/charts/apex/line', 'styles' => []],
        'charts-apex-mixed' => ['family' => 'apex', 'page' => 'mixed', 'file' => 'charts-apex-mixed.html', 'label' => 'Mixed', 'route' => '/demo-ui/charts/apex/mixed', 'styles' => []],
        'charts-apex-timeline' => ['family' => 'apex', 'page' => 'timeline', 'file' => 'charts-apex-timeline.html', 'label' => 'Timeline', 'route' => '/demo-ui/charts/apex/timeline', 'styles' => []],
        'charts-apex-boxplot' => ['family' => 'apex', 'page' => 'boxplot', 'file' => 'charts-apex-boxplot.html', 'label' => 'Boxplot', 'route' => '/demo-ui/charts/apex/boxplot', 'styles' => []],
        'charts-apex-treemap' => ['family' => 'apex', 'page' => 'treemap', 'file' => 'charts-apex-treemap.html', 'label' => 'Treemap', 'route' => '/demo-ui/charts/apex/treemap', 'styles' => []],
        'charts-apex-pie' => ['family' => 'apex', 'page' => 'pie', 'file' => 'charts-apex-pie.html', 'label' => 'Pie', 'route' => '/demo-ui/charts/apex/pie', 'styles' => []],
        'charts-apex-radar' => ['family' => 'apex', 'page' => 'radar', 'file' => 'charts-apex-radar.html', 'label' => 'Radar', 'route' => '/demo-ui/charts/apex/radar', 'styles' => []],
        'charts-apex-radialbar' => ['family' => 'apex', 'page' => 'radialbar', 'file' => 'charts-apex-radialbar.html', 'label' => 'RadialBar', 'route' => '/demo-ui/charts/apex/radialbar', 'styles' => []],
        'charts-apex-scatter' => ['family' => 'apex', 'page' => 'scatter', 'file' => 'charts-apex-scatter.html', 'label' => 'Scatter', 'route' => '/demo-ui/charts/apex/scatter', 'styles' => []],
        'charts-apex-polar-area' => ['family' => 'apex', 'page' => 'polar-area', 'file' => 'charts-apex-polar-area.html', 'label' => 'Polar Area', 'route' => '/demo-ui/charts/apex/polar-area', 'styles' => []],
        'charts-apex-sparklines' => ['family' => 'apex', 'page' => 'sparklines', 'file' => 'charts-apex-sparklines.html', 'label' => 'Sparklines', 'route' => '/demo-ui/charts/apex/sparklines', 'styles' => []],
        'charts-apex-range' => ['family' => 'apex', 'page' => 'range', 'file' => 'charts-apex-range.html', 'label' => 'Range', 'route' => '/demo-ui/charts/apex/range', 'styles' => []],
        'charts-apex-funnel' => ['family' => 'apex', 'page' => 'funnel', 'file' => 'charts-apex-funnel.html', 'label' => 'Funnel', 'route' => '/demo-ui/charts/apex/funnel', 'styles' => []],
        'charts-apex-slope' => ['family' => 'apex', 'page' => 'slope', 'file' => 'charts-apex-slope.html', 'label' => 'Slope', 'route' => '/demo-ui/charts/apex/slope', 'styles' => []],
        'charts-echart-line' => ['family' => 'echart', 'page' => 'line', 'file' => 'charts-echart-line.html', 'label' => 'Line', 'route' => '/demo-ui/charts/echart/line', 'styles' => []],
        'charts-echart-bar' => ['family' => 'echart', 'page' => 'bar', 'file' => 'charts-echart-bar.html', 'label' => 'Bar', 'route' => '/demo-ui/charts/echart/bar', 'styles' => []],
        'charts-echart-pie' => ['family' => 'echart', 'page' => 'pie', 'file' => 'charts-echart-pie.html', 'label' => 'Pie', 'route' => '/demo-ui/charts/echart/pie', 'styles' => []],
        'charts-echart-scatter' => ['family' => 'echart', 'page' => 'scatter', 'file' => 'charts-echart-scatter.html', 'label' => 'Scatter', 'route' => '/demo-ui/charts/echart/scatter', 'styles' => []],
        'charts-echart-geo-map' => ['family' => 'echart', 'page' => 'geo-map', 'file' => 'charts-echart-geo-map.html', 'label' => 'GEO Map', 'route' => '/demo-ui/charts/echart/geo-map', 'styles' => []],
        'charts-echart-gauge' => ['family' => 'echart', 'page' => 'gauge', 'file' => 'charts-echart-gauge.html', 'label' => 'Gauge', 'route' => '/demo-ui/charts/echart/gauge', 'styles' => []],
        'charts-echart-candlestick' => ['family' => 'echart', 'page' => 'candlestick', 'file' => 'charts-echart-candlestick.html', 'label' => 'Candlestick', 'route' => '/demo-ui/charts/echart/candlestick', 'styles' => []],
        'charts-echart-area' => ['family' => 'echart', 'page' => 'area', 'file' => 'charts-echart-area.html', 'label' => 'Area', 'route' => '/demo-ui/charts/echart/area', 'styles' => []],
        'charts-echart-radar' => ['family' => 'echart', 'page' => 'radar', 'file' => 'charts-echart-radar.html', 'label' => 'Radar', 'route' => '/demo-ui/charts/echart/radar', 'styles' => []],
        'charts-echart-heatmap' => ['family' => 'echart', 'page' => 'heatmap', 'file' => 'charts-echart-heatmap.html', 'label' => 'Heatmap', 'route' => '/demo-ui/charts/echart/heatmap', 'styles' => []],
        'charts-echart-other' => ['family' => 'echart', 'page' => 'other', 'file' => 'charts-echart-other.html', 'label' => 'Other', 'route' => '/demo-ui/charts/echart/other', 'styles' => []],
    ];
    private const TABLE_FAMILIES = [
        'datatables' => [
            'label' => 'DataTables',
            'badge' => '15',
            'slugs' => [
                'datatables-basic',
                'datatables-export-data',
                'datatables-select',
                'datatables-ajax',
                'datatables-javascript',
                'datatables-rendering',
                'datatables-scroll',
                'datatables-fixed-columns',
                'datatables-fixed-header',
                'datatables-columns',
                'datatables-child-rows',
                'datatables-column-searching',
                'datatables-range-search',
                'datatables-rows-add',
                'datatables-checkbox-select',
            ],
        ],
    ];
    private const TABLE_PAGES = [
        'static' => [
            'file' => 'tables-static.html',
            'label' => 'Static Tables',
            'route' => '/demo-ui/tables/static',
            'styles' => [],
        ],
        'custom' => [
            'file' => 'tables-custom.html',
            'label' => 'Custom Tables',
            'route' => '/demo-ui/tables/custom',
            'styles' => [],
        ],
        'datatables-basic' => [
            'family' => 'datatables',
            'file' => 'tables-datatables-basic.html',
            'label' => 'Basic',
            'route' => '/demo-ui/tables/datatables/basic',
            'styles' => [
                '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.css',
            ],
        ],
        'datatables-export-data' => [
            'family' => 'datatables',
            'file' => 'tables-datatables-export-data.html',
            'label' => 'Export Data',
            'route' => '/demo-ui/tables/datatables/export-data',
            'styles' => [
                '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.css',
                '/assets/vendor/inspinia/plugins/datatables/buttons.bootstrap5.min.css',
            ],
        ],
        'datatables-select' => [
            'family' => 'datatables',
            'file' => 'tables-datatables-select.html',
            'label' => 'Select',
            'route' => '/demo-ui/tables/datatables/select',
            'styles' => [
                '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.css',
                '/assets/vendor/inspinia/plugins/datatables/select.bootstrap5.min.css',
            ],
        ],
        'datatables-ajax' => [
            'family' => 'datatables',
            'file' => 'tables-datatables-ajax.html',
            'label' => 'Ajax',
            'route' => '/demo-ui/tables/datatables/ajax',
            'styles' => [
                '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.css',
            ],
        ],
        'datatables-javascript' => [
            'family' => 'datatables',
            'file' => 'tables-datatables-javascript.html',
            'label' => 'Javascript Source',
            'route' => '/demo-ui/tables/datatables/javascript',
            'styles' => [
                '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.css',
            ],
        ],
        'datatables-rendering' => [
            'family' => 'datatables',
            'file' => 'tables-datatables-rendering.html',
            'label' => 'Data Rendering',
            'route' => '/demo-ui/tables/datatables/rendering',
            'styles' => [
                '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.css',
            ],
        ],
        'datatables-scroll' => [
            'family' => 'datatables',
            'file' => 'tables-datatables-scroll.html',
            'label' => 'Scroll',
            'route' => '/demo-ui/tables/datatables/scroll',
            'styles' => [
                '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.css',
            ],
        ],
        'datatables-fixed-columns' => [
            'family' => 'datatables',
            'file' => 'tables-datatables-fixed-columns.html',
            'label' => 'Fixed Columns',
            'route' => '/demo-ui/tables/datatables/fixed-columns',
            'styles' => [
                '/assets/vendor/inspinia/plugins/datatables/fixedColumns.bootstrap5.min.css',
            ],
        ],
        'datatables-fixed-header' => [
            'family' => 'datatables',
            'file' => 'tables-datatables-fixed-header.html',
            'label' => 'Fixed Header',
            'route' => '/demo-ui/tables/datatables/fixed-header',
            'styles' => [
                '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.css',
                '/assets/vendor/inspinia/plugins/datatables/fixedHeader.bootstrap5.min.css',
            ],
        ],
        'datatables-columns' => [
            'family' => 'datatables',
            'file' => 'tables-datatables-columns.html',
            'label' => 'Show & Hide Column',
            'route' => '/demo-ui/tables/datatables/columns',
            'styles' => [
                '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.css',
            ],
        ],
        'datatables-child-rows' => [
            'family' => 'datatables',
            'file' => 'tables-datatables-child-rows.html',
            'label' => 'Child Rows',
            'route' => '/demo-ui/tables/datatables/child-rows',
            'styles' => [
                '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.css',
            ],
        ],
        'datatables-column-searching' => [
            'family' => 'datatables',
            'file' => 'tables-datatables-column-searching.html',
            'label' => 'Column Searching',
            'route' => '/demo-ui/tables/datatables/column-searching',
            'styles' => [
                '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.css',
            ],
        ],
        'datatables-range-search' => [
            'family' => 'datatables',
            'file' => 'tables-datatables-range-search.html',
            'label' => 'Range Search',
            'route' => '/demo-ui/tables/datatables/range-search',
            'styles' => [
                '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.css',
            ],
        ],
        'datatables-rows-add' => [
            'family' => 'datatables',
            'file' => 'tables-datatables-rows-add.html',
            'label' => 'Add Rows',
            'route' => '/demo-ui/tables/datatables/rows-add',
            'styles' => [
                '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.css',
            ],
        ],
        'datatables-checkbox-select' => [
            'family' => 'datatables',
            'file' => 'tables-datatables-checkbox-select.html',
            'label' => 'Checkbox Select',
            'route' => '/demo-ui/tables/datatables/checkbox-select',
            'styles' => [
                '/assets/vendor/inspinia/plugins/datatables/select.bootstrap5.min.css',
                '/assets/vendor/inspinia/plugins/datatables/responsive.bootstrap5.min.css',
            ],
        ],
    ];

    /**
     * @return array<string, array<int, array{file:string,label:string}>>
     */
    private function demoUiSections(): array
    {
        return [
            'framework-configuration' => [
                ['file' => '/configuration/environment-setup', 'label' => 'Environment Setup'],
                ['file' => '/configuration/application-health', 'label' => 'Application Health'],
                ['file' => '/configuration/platform-appearance', 'label' => 'Platform Appearance'],
                ['file' => '/configuration/feature-flags', 'label' => 'Feature Flags'],
                ['file' => '/configuration/plugins', 'label' => 'Plugins Management'],
            ],
            'framework-workspaces' => [
                ['file' => '/workspaces/catalogs', 'label' => 'Catalogs'],
                ['file' => '/workspaces/module-designer', 'label' => 'Module Designer'],
                ['file' => '/workspaces/media-fields', 'label' => 'Media and Documents Fields'],
                ['file' => '/workspaces/media-library', 'label' => 'Media and Documents Library'],
                ['file' => '/workspaces/document-templates', 'label' => 'Document Template'],
                ['file' => '/workspaces/locale-tools', 'label' => 'Locale Tools'],
            ],
            'framework-operations' => [
                ['file' => '/operations/deployments', 'label' => 'Deployments'],
                ['file' => '/operations/tenancy', 'label' => 'Tenancy'],
            ],
            'framework-users' => [
                ['file' => '/users', 'label' => 'User Management'],
                ['file' => '/users/roles', 'label' => 'User Role'],
                ['file' => '/users/permissions', 'label' => 'User Permissions'],
                ['file' => '/users/enroll', 'label' => 'User Enroll'],
            ],
            'base-ui' => [
                ['file' => 'ui-accordions.html', 'label' => 'Accordions'],
                ['file' => 'ui-alerts.html', 'label' => 'Alerts'],
                ['file' => 'ui-images.html', 'label' => 'Images'],
                ['file' => 'ui-badges.html', 'label' => 'Badges'],
                ['file' => 'ui-breadcrumb.html', 'label' => 'Breadcrumb'],
                ['file' => 'ui-buttons.html', 'label' => 'Buttons'],
                ['file' => 'ui-cards.html', 'label' => 'Cards'],
                ['file' => 'ui-carousel.html', 'label' => 'Carousel'],
                ['file' => 'ui-collapse.html', 'label' => 'Collapse'],
                ['file' => 'ui-colors.html', 'label' => 'Colors'],
                ['file' => 'ui-dropdowns.html', 'label' => 'Dropdowns'],
                ['file' => 'ui-videos.html', 'label' => 'Videos'],
                ['file' => 'ui-grid.html', 'label' => 'Grid Options'],
                ['file' => 'ui-links.html', 'label' => 'Links'],
                ['file' => 'ui-list-group.html', 'label' => 'List Group'],
                ['file' => 'ui-modals.html', 'label' => 'Modals'],
                ['file' => 'ui-notifications.html', 'label' => 'Notifications'],
                ['file' => 'ui-offcanvas.html', 'label' => 'Offcanvas'],
                ['file' => 'ui-placeholders.html', 'label' => 'Placeholders'],
                ['file' => 'ui-pagination.html', 'label' => 'Pagination'],
                ['file' => 'ui-popovers.html', 'label' => 'Popovers'],
                ['file' => 'ui-progress.html', 'label' => 'Progress'],
                ['file' => 'ui-scrollspy.html', 'label' => 'Scrollspy'],
                ['file' => 'ui-spinners.html', 'label' => 'Spinners'],
                ['file' => 'ui-tabs.html', 'label' => 'Tabs'],
                ['file' => 'ui-tooltips.html', 'label' => 'Tooltips'],
                ['file' => 'ui-typography.html', 'label' => 'Typography'],
                ['file' => 'ui-utilities.html', 'label' => 'Utilities'],
            ],
            'widgets' => [
                ['file' => 'ui-cards.html', 'label' => 'Cards'],
                ['file' => 'ui-notifications.html', 'label' => 'Notifications'],
                ['file' => 'ui-placeholders.html', 'label' => 'Placeholders'],
            ],
            'metrics' => [
                ['file' => 'ui-progress.html', 'label' => 'Progress'],
                ['file' => 'ui-spinners.html', 'label' => 'Spinners'],
                ['file' => 'ui-badges.html', 'label' => 'Badges'],
            ],
            'charts' => [
                ...$this->buildChartSectionItems(),
            ],
            'forms' => [
                ['file' => 'form-elements.html', 'label' => 'Basic Elements'],
                ['file' => 'form-pickers.html', 'label' => 'Pickers'],
                ['file' => 'form-select.html', 'label' => 'Select'],
                ['file' => 'form-validation.html', 'label' => 'Validation'],
                ['file' => 'form-wizard.html', 'label' => 'Wizard'],
                ['file' => 'form-fileuploads.html', 'label' => 'File Uploads'],
                ['file' => 'form-text-editors.html', 'label' => 'Text Editors'],
                ['file' => 'form-range-slider.html', 'label' => 'Range Slider'],
            ],
            'tables' => [
                ...$this->buildTableSectionItems(),
            ],
        ];
    }

    /**
     * Handles the index workflow.
     */
    public function index(): Response
    {
        $sections = $this->demoUiSections();
        $requestedFile = trim((string) $this->input('doc', self::DEFAULT_DOC_FILE));
        $selectedFile = self::DEFAULT_DOC_FILE;
        $selectedSection = 'base-ui';
        $selectedLabel = 'Alerts';

        foreach ($sections as $sectionKey => $items) {
            foreach ($items as $item) {
                if ($item['file'] !== $requestedFile) {
                    continue;
                }

                $selectedFile = $item['file'];
                $selectedSection = $sectionKey;
                $selectedLabel = $item['label'];
                break 2;
            }
        }

        return $this->renderPage(
            $selectedFile,
            $selectedSection,
            $selectedLabel,
            $this->resolveFormPageSlugByFile($selectedFile)
        );
    }

    /**
     * Handles the basic elements workflow.
     */
    public function basicElements(): Response
    {
        return $this->renderFormPage('basic-elements');
    }

    /**
     * Handles the pickers workflow.
     */
    public function pickers(): Response
    {
        return $this->renderFormPage('pickers');
    }

    /**
     * Handles the select workflow.
     */
    public function select(): Response
    {
        return $this->renderFormPage('select');
    }

    /**
     * Handles the validation workflow.
     */
    public function validation(): Response
    {
        return $this->renderFormPage('validation');
    }

    /**
     * Handles the wizard workflow.
     */
    public function wizard(): Response
    {
        return $this->renderFormPage('wizard');
    }

    /**
     * Handles the file uploads workflow.
     */
    public function fileUploads(): Response
    {
        return $this->renderFormPage('file-uploads');
    }

    /**
     * Handles the text editors workflow.
     */
    public function textEditors(): Response
    {
        return $this->renderFormPage('text-editors');
    }

    /**
     * Handles the range slider workflow.
     */
    public function rangeSlider(): Response
    {
        return $this->renderFormPage('range-slider');
    }

    /**
     * Handles the alerts workflow.
     */
    public function alerts(): Response
    {
        return $this->renderBaseUiPage('alerts');
    }

    /**
     * Handles the accordions workflow.
     */
    public function accordions(): Response
    {
        return $this->renderBaseUiPage('accordions');
    }

    /**
     * Handles the badges workflow.
     */
    public function badges(): Response
    {
        return $this->renderBaseUiPage('badges');
    }

    /**
     * Handles the breadcrumb workflow.
     */
    public function breadcrumb(): Response
    {
        return $this->renderBaseUiPage('breadcrumb');
    }

    /**
     * Handles the buttons workflow.
     */
    public function buttons(): Response
    {
        return $this->renderBaseUiPage('buttons');
    }

    /**
     * Handles the cards workflow.
     */
    public function cards(): Response
    {
        return $this->renderBaseUiPage('cards');
    }

    /**
     * Handles the carousel workflow.
     */
    public function carousel(): Response
    {
        return $this->renderBaseUiPage('carousel');
    }

    /**
     * Handles the collapse workflow.
     */
    public function collapse(): Response
    {
        return $this->renderBaseUiPage('collapse');
    }

    /**
     * Handles the colors workflow.
     */
    public function colors(): Response
    {
        return $this->renderBaseUiPage('colors');
    }

    /**
     * Handles the dropdowns workflow.
     */
    public function dropdowns(): Response
    {
        return $this->renderBaseUiPage('dropdowns');
    }

    /**
     * Handles the grid options workflow.
     */
    public function gridOptions(): Response
    {
        return $this->renderBaseUiPage('grid-options');
    }

    /**
     * Handles the images workflow.
     */
    public function images(): Response
    {
        return $this->renderBaseUiPage('images');
    }

    /**
     * Handles the links workflow.
     */
    public function links(): Response
    {
        return $this->renderBaseUiPage('links');
    }

    /**
     * Handles the list group workflow.
     */
    public function listGroup(): Response
    {
        return $this->renderBaseUiPage('list-group');
    }

    /**
     * Handles the modals workflow.
     */
    public function modals(): Response
    {
        return $this->renderBaseUiPage('modals');
    }

    /**
     * Handles the notifications workflow.
     */
    public function notifications(): Response
    {
        return $this->renderBaseUiPage('notifications');
    }

    /**
     * Handles the offcanvas workflow.
     */
    public function offcanvas(): Response
    {
        return $this->renderBaseUiPage('offcanvas');
    }

    /**
     * Handles the pagination workflow.
     */
    public function pagination(): Response
    {
        return $this->renderBaseUiPage('pagination');
    }

    /**
     * Handles the placeholders workflow.
     */
    public function placeholders(): Response
    {
        return $this->renderBaseUiPage('placeholders');
    }

    /**
     * Handles the progress workflow.
     */
    public function progress(): Response
    {
        return $this->renderBaseUiPage('progress');
    }

    /**
     * Handles the popovers workflow.
     */
    public function popovers(): Response
    {
        return $this->renderBaseUiPage('popovers');
    }

    /**
     * Handles the scrollspy workflow.
     */
    public function scrollspy(): Response
    {
        return $this->renderBaseUiPage('scrollspy');
    }

    /**
     * Handles the spinners workflow.
     */
    public function spinners(): Response
    {
        return $this->renderBaseUiPage('spinners');
    }

    /**
     * Handles the tabs workflow.
     */
    public function tabs(): Response
    {
        return $this->renderBaseUiPage('tabs');
    }

    /**
     * Handles the tooltips workflow.
     */
    public function tooltips(): Response
    {
        return $this->renderBaseUiPage('tooltips');
    }

    /**
     * Handles the typography workflow.
     */
    public function typography(): Response
    {
        return $this->renderBaseUiPage('typography');
    }

    /**
     * Handles the utilities workflow.
     */
    public function utilities(): Response
    {
        return $this->renderBaseUiPage('utilities');
    }

    /**
     * Handles the videos workflow.
     */
    public function videos(): Response
    {
        return $this->renderBaseUiPage('videos');
    }

    /**
     * Handles the charts workflow.
     */
    public function charts(string $family, string $page): Response
    {
        return $this->renderChartPage($family, $page);
    }

    /**
     * Handles the table workflow.
     */
    public function table(string $page): Response
    {
        return $this->renderTablePage($page);
    }

    /**
     * Handles the data table workflow.
     */
    public function dataTable(string $page): Response
    {
        return $this->renderDataTablePage($page);
    }

    /**
     * Renders the current view state.
     */
    private function renderBaseUiPage(string $pageSlug): Response
    {
        return $this->renderMappedPage(self::BASE_UI_PAGES, 'base-ui', $pageSlug);
    }

    /**
     * Renders the current view state.
     */
    private function renderFormPage(string $pageSlug): Response
    {
        return $this->renderMappedPage(self::FORM_PAGES, 'forms', $pageSlug);
    }

    /**
     * Renders the current view state.
     */
    private function renderChartPage(string $family, string $page): Response
    {
        return $this->renderMappedPage(self::CHART_PAGES, 'charts', 'charts-' . $family . '-' . $page);
    }

    /**
     * Renders the current view state.
     */
    private function renderTablePage(string $pageSlug): Response
    {
        return $this->renderMappedPage(self::TABLE_PAGES, 'tables', $pageSlug);
    }

    /**
     * Renders the current view state.
     */
    private function renderDataTablePage(string $page): Response
    {
        return $this->renderTablePage('datatables-' . $page);
    }

    /**
     * @param array<string, array{file:string,label:string,route:string,styles:array<int, string>}> $catalog
     */
    private function renderMappedPage(array $catalog, string $sectionKey, string $pageSlug): Response
    {
        $page = $catalog[$pageSlug] ?? null;
        if (!is_array($page)) {
            return new Response('Demo UI page not found.', 404, [
                'Content-Type' => 'text/plain; charset=UTF-8',
            ]);
        }

        return $this->renderPage(
            (string) $page['file'],
            $sectionKey,
            (string) $page['label'],
            $pageSlug
        );
    }

    /**
     * Renders the current view state.
     */
    private function renderPage(string $selectedFile, string $selectedSection, string $selectedLabel, ?string $pageSlug = null): Response
    {
        $frontDir = PD . DS . 'Repository' . DS . 'Framework' . DS . 'DemoUi' . DS . 'front';
        $styleVersion = (string) (@filemtime($frontDir . DS . 'style.css') ?: time());
        $scriptVersion = (string) (@filemtime($frontDir . DS . 'script.js') ?: time());
        $sections = $this->demoUiSections();
        $navGroups = $this->buildNavGroups($sections, $selectedFile, $selectedSection);
        $pageStyles = $this->resolvePageStyles($pageSlug);

        $authUser = AuthManager::getInstance()->user() ?? [];

        return $this->view('demoui.demo-ui', [
            'title' => 'Demo UI',
            'pageTitle' => 'Demo UI',
            'csp_nonce' => CspNonce::get(),
            'auth_name' => trim((string) ($authUser['name'] ?? '')),
            'status_bar_show_theme_toggle' => true,
            'status_bar_theme_toggle_attribute' => 'data-demoui-theme-toggle',
            'status_bar_theme_toggle_icon_class' => 'ti ti-moon',
            'status_bar_show_customizer_toggle' => true,
            'status_bar_customizer_toggle_attribute' => 'data-theme-customizer-toggle',
            'status_bar_customizer_toggle_icon_class' => 'ti ti-settings',
            'status_bar_customizer_toggle_aria_label' => 'Open Admin Customizer',
            'status_bar_customizer_toggle_title' => 'Admin Customizer',
            'styles' => [
                '/assets/css/catalyst/status-bar.css',
                ...$pageStyles,
                '/assets/css/work/demoui/style.css?v=' . rawurlencode($styleVersion),
            ],
            'scripts' => [
                [
                    'src' => '/assets/js/work/demoui/script.js?v=' . rawurlencode($scriptVersion),
                    'defer' => true,
                ],
            ],
            'demo_ui_nav_groups' => $navGroups,
            'selected_doc_file' => $selectedFile,
            'selected_doc_label' => $selectedLabel,
            'selected_doc_section' => $selectedSection,
            'selected_doc_source_url' => self::THEME_BASE_URL . $selectedFile,
            'demo_ui_page_slug' => $pageSlug ?? '',
            'preview_html' => TrustedHtml::fromString($this->loadThemePreviewHtml($selectedFile)),
        ], 200, 'demo-ui-shell')->setAttribute('csp_profile', 'trusted-renderer');
    }

    /**
     * @param array<int, array{file:string,label:string}> $items
     * @return array<int, array{file:string,label:string,href:string,is_active:bool,link_class:string}>
     */
    private function buildNavItems(array $items, string $selectedFile): array
    {
        $navItems = [];
        foreach ($items as $item) {
            $isActive = $item['file'] === $selectedFile;
            $navItems[] = [
                'file' => $item['file'],
                'label' => $item['label'],
                'href' => $this->resolveNavHref($item['file']),
                'is_active' => $isActive,
                'link_class' => $isActive ? 'demo-ui-nav__link active' : 'demo-ui-nav__link',
            ];
        }

        return $navItems;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildChartNavItems(string $selectedFile): array
    {
        $items = [];

        foreach (self::CHART_FAMILIES as $family => $definition) {
            $childItems = [];
            $isFamilyActive = false;

            foreach ($definition['slugs'] as $slug) {
                $page = self::CHART_PAGES[$slug] ?? null;
                if (!is_array($page)) {
                    continue;
                }

                $isActive = (string) ($page['file'] ?? '') === $selectedFile;
                $isFamilyActive = $isFamilyActive || $isActive;
                $childItems[] = [
                    'file' => (string) $page['file'],
                    'label' => (string) $page['label'],
                    'href' => (string) $page['route'],
                    'is_active' => $isActive,
                    'link_class' => $isActive ? 'side-nav-link active' : 'side-nav-link',
                ];
            }

            $items[] = [
                'label' => (string) ($definition['label'] ?? ucfirst($family)),
                'is_nested_collapse' => true,
                'is_active' => $isFamilyActive,
                'link_class' => $isFamilyActive ? 'side-nav-link active' : 'side-nav-link',
                'collapse_id' => 'demo-charts-' . $family,
                'expanded' => $isFamilyActive ? 'true' : 'false',
                'show' => $isFamilyActive,
                'children' => $childItems,
            ];
        }

        return $items;
    }

    /**
     * @return array<int, array{file:string,label:string}>
     */
    private function buildChartSectionItems(): array
    {
        $items = [];

        foreach (self::CHART_FAMILIES as $definition) {
            foreach ($definition['slugs'] as $slug) {
                $page = self::CHART_PAGES[$slug] ?? null;
                if (!is_array($page)) {
                    continue;
                }

                $items[] = [
                    'file' => (string) $page['file'],
                    'label' => (string) $page['label'],
                ];
            }
        }

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildTableNavItems(string $selectedFile): array
    {
        $items = [];

        foreach (['static', 'custom'] as $slug) {
            $page = self::TABLE_PAGES[$slug] ?? null;
            if (!is_array($page)) {
                continue;
            }

            $isActive = (string) ($page['file'] ?? '') === $selectedFile;
            $items[] = [
                'file' => (string) $page['file'],
                'label' => (string) $page['label'],
                'href' => (string) $page['route'],
                'is_active' => $isActive,
                'link_class' => $isActive ? 'side-nav-link active' : 'side-nav-link',
            ];
        }

        foreach (self::TABLE_FAMILIES as $family => $definition) {
            $childItems = [];
            $isFamilyActive = false;

            foreach ((array) ($definition['slugs'] ?? []) as $slug) {
                $page = self::TABLE_PAGES[$slug] ?? null;
                if (!is_array($page)) {
                    continue;
                }

                $isActive = (string) ($page['file'] ?? '') === $selectedFile;
                $isFamilyActive = $isFamilyActive || $isActive;
                $childItems[] = [
                    'file' => (string) $page['file'],
                    'label' => (string) $page['label'],
                    'href' => (string) $page['route'],
                    'is_active' => $isActive,
                    'link_class' => $isActive ? 'side-nav-link active' : 'side-nav-link',
                ];
            }

            $items[] = [
                'label' => (string) ($definition['label'] ?? ucfirst($family)),
                'badge_label' => (string) ($definition['badge'] ?? ''),
                'badge_class' => 'badge bg-success text-white',
                'is_nested_collapse' => true,
                'is_active' => $isFamilyActive,
                'link_class' => $isFamilyActive ? 'side-nav-link active' : 'side-nav-link',
                'collapse_id' => 'demo-tables-' . $family,
                'expanded' => $isFamilyActive ? 'true' : 'false',
                'show' => $isFamilyActive,
                'children' => $childItems,
            ];
        }

        return $items;
    }

    /**
     * @return array<int, array{file:string,label:string}>
     */
    private function buildTableSectionItems(): array
    {
        $items = [];

        foreach (['static', 'custom'] as $slug) {
            $page = self::TABLE_PAGES[$slug] ?? null;
            if (!is_array($page)) {
                continue;
            }

            $items[] = [
                'file' => (string) $page['file'],
                'label' => (string) $page['label'],
            ];
        }

        foreach (self::TABLE_FAMILIES as $definition) {
            foreach ((array) ($definition['slugs'] ?? []) as $slug) {
                $page = self::TABLE_PAGES[$slug] ?? null;
                if (!is_array($page)) {
                    continue;
                }

                $items[] = [
                    'file' => (string) $page['file'],
                    'label' => (string) $page['label'],
                ];
            }
        }

        return $items;
    }

    /**
     * Resolves the requested value.
     */
    private function resolveNavHref(string $file): string
    {
        if (str_starts_with($file, '/')) {
            return $file;
        }

        foreach ([self::BASE_UI_PAGES, self::FORM_PAGES, self::CHART_PAGES, self::TABLE_PAGES] as $catalog) {
            foreach ($catalog as $page) {
                if (($page['file'] ?? null) === $file) {
                    return (string) ($page['route'] ?? '#!');
                }
            }
        }

        return '#!';
    }

    /**
     * @param array<string, array<int, array{file:string,label:string}>> $sections
     * @return array<int, array<string, mixed>>
     */
    private function buildNavGroups(array $sections, string $selectedFile, string $selectedSection): array
    {
        $definitions = [
            ['kind' => 'title', 'label' => 'Framework Configuration'],
            ['kind' => 'collapse', 'key' => 'framework-configuration', 'label' => 'Configuration', 'icon' => 'ti ti-settings-cog'],
            ['kind' => 'title', 'label' => 'Framework Operations'],
            ['kind' => 'collapse', 'key' => 'framework-workspaces', 'label' => 'Workspaces', 'icon' => 'ti ti-layout-grid'],
            ['kind' => 'collapse', 'key' => 'framework-operations', 'label' => 'Operations', 'icon' => 'ti ti-briefcase-2'],
            ['kind' => 'collapse', 'key' => 'framework-users', 'label' => 'Users', 'icon' => 'ti ti-users'],
            ['kind' => 'title', 'label' => 'Devtools'],
            ['kind' => 'title', 'label' => 'Components'],
            ['kind' => 'collapse', 'key' => 'base-ui', 'label' => 'Base UI', 'icon' => 'ti ti-diamonds'],
            ['kind' => 'collapse', 'key' => 'charts', 'label' => 'Charts', 'icon' => 'ti ti-chart-donut'],
            ['kind' => 'collapse', 'key' => 'forms', 'label' => 'Forms', 'icon' => 'ti ti-clipboard-text'],
            ['kind' => 'collapse', 'key' => 'tables', 'label' => 'Tables', 'icon' => 'ti ti-table-options'],
        ];

        $groups = [];

        foreach ($definitions as $definition) {
            if ($definition['kind'] === 'title') {
                $groups[] = [
                    'is_title' => true,
                    'label' => $definition['label'],
                ];
                continue;
            }

            $key = (string) $definition['key'];
            $items = match ($key) {
                'charts' => $this->buildChartNavItems($selectedFile),
                'tables' => $this->buildTableNavItems($selectedFile),
                default => $this->buildNavItems($sections[$key] ?? [], $selectedFile),
            };
            $isActive = $selectedSection === $key;

            if ($key === 'framework-configuracion') {
                foreach ($items as $index => &$item) {
                    $item['is_active'] = $index === 0;
                }
                unset($item);
                $isActive = true;
            }

            $groups[] = [
                'is_title' => false,
                'is_collapse' => true,
                'key' => $key,
                'label' => $definition['label'],
                'icon' => $definition['icon'],
                'collapse_id' => 'demo-' . $key,
                'is_active' => $isActive,
                'expanded' => $isActive ? 'true' : 'false',
                'show' => $isActive,
                'items' => $items,
            ];
        }

        return $groups;
    }

    /**
     * Loads the requested data.
     */
    private function loadThemePreviewHtml(string $file): string
    {
        $previewPath = PD . DS . 'Repository' . DS . 'Framework' . DS . 'DemoUi' . DS . 'generated' . DS . 'theme-previews' . DS . basename($file);
        if (!is_file($previewPath)) {
            return '<div class="alert alert-danger mb-0">Theme source not found.</div>';
        }

        $content = (string) file_get_contents($previewPath);
        if ($content === '') {
            return '<div class="alert alert-warning mb-0">Theme preview is empty.</div>';
        }

        return $content;
    }

    /**
     * Resolves the requested value.
     */
    private function resolveFormPageSlugByFile(string $file): ?string
    {
        foreach ([self::FORM_PAGES, self::BASE_UI_PAGES, self::CHART_PAGES, self::TABLE_PAGES] as $catalog) {
            foreach ($catalog as $slug => $page) {
                if (($page['file'] ?? null) === $file) {
                    return $slug;
                }
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function resolvePageStyles(?string $pageSlug): array
    {
        if ($pageSlug === null || $pageSlug === '') {
            return [];
        }

        $page = self::FORM_PAGES[$pageSlug]
            ?? self::BASE_UI_PAGES[$pageSlug]
            ?? self::CHART_PAGES[$pageSlug]
            ?? self::TABLE_PAGES[$pageSlug]
            ?? null;
        if (!is_array($page)) {
            return [];
        }

        $styles = $page['styles'] ?? [];
        if (!is_array($styles)) {
            return [];
        }

        return array_values(array_filter($styles, static fn ($style): bool => is_string($style) && $style !== ''));
    }
}
