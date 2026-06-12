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
use Catalyst\Framework\Appearance\PlatformAppearanceManager;
use Catalyst\Framework\Controllers\Controller;
use Catalyst\Framework\Http\Response;
use Catalyst\Framework\View\InlineJson;
use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CspNonce;

/**
 * Presents the public INSPINIA reference surface and its preview catalog.
 *
 * @package Catalyst\Repository\DemoUi\Controllers
 * Responsibility: Resolves demo routes, catalog selection data and trusted generated previews.
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
     * Returns navigation sections exposed by the Demo UI reference surface.
     *
     * Responsibility: Returns navigation sections exposed by the Demo UI reference surface.
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
     * Renders the requested Demo UI document or the default alerts preview.
     *
     * Responsibility: Renders the requested Demo UI document or the default alerts preview.
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
     * Renders the basic form-elements reference page.
     *
     * Responsibility: Renders the basic form-elements reference page.
     */
    public function basicElements(): Response
    {
        return $this->renderFormPage('basic-elements');
    }

    /**
     * Renders the form-pickers reference page.
     *
     * Responsibility: Renders the form-pickers reference page.
     */
    public function pickers(): Response
    {
        return $this->renderFormPage('pickers');
    }

    /**
     * Renders the form-select reference page.
     *
     * Responsibility: Renders the form-select reference page.
     */
    public function select(): Response
    {
        return $this->renderFormPage('select');
    }

    /**
     * Renders the form-validation reference page.
     *
     * Responsibility: Renders the form-validation reference page.
     */
    public function validation(): Response
    {
        return $this->renderFormPage('validation');
    }

    /**
     * Renders the form-wizard reference page.
     *
     * Responsibility: Renders the form-wizard reference page.
     */
    public function wizard(): Response
    {
        return $this->renderFormPage('wizard');
    }

    /**
     * Renders the file-upload reference page.
     *
     * Responsibility: Renders the file-upload reference page.
     */
    public function fileUploads(): Response
    {
        return $this->renderFormPage('file-uploads');
    }

    /**
     * Renders the text-editor reference page.
     *
     * Responsibility: Renders the text-editor reference page.
     */
    public function textEditors(): Response
    {
        return $this->renderFormPage('text-editors');
    }

    /**
     * Renders the range-slider reference page.
     *
     * Responsibility: Renders the range-slider reference page.
     */
    public function rangeSlider(): Response
    {
        return $this->renderFormPage('range-slider');
    }

    /**
     * Renders the alerts component reference page.
     *
     * Responsibility: Renders the alerts component reference page.
     */
    public function alerts(): Response
    {
        return $this->renderBaseUiPage('alerts');
    }

    /**
     * Renders the accordions component reference page.
     *
     * Responsibility: Renders the accordions component reference page.
     */
    public function accordions(): Response
    {
        return $this->renderBaseUiPage('accordions');
    }

    /**
     * Renders the badges component reference page.
     *
     * Responsibility: Renders the badges component reference page.
     */
    public function badges(): Response
    {
        return $this->renderBaseUiPage('badges');
    }

    /**
     * Renders the breadcrumb component reference page.
     *
     * Responsibility: Renders the breadcrumb component reference page.
     */
    public function breadcrumb(): Response
    {
        return $this->renderBaseUiPage('breadcrumb');
    }

    /**
     * Renders the buttons component reference page.
     *
     * Responsibility: Renders the buttons component reference page.
     */
    public function buttons(): Response
    {
        return $this->renderBaseUiPage('buttons');
    }

    /**
     * Renders the cards component reference page.
     *
     * Responsibility: Renders the cards component reference page.
     */
    public function cards(): Response
    {
        return $this->renderBaseUiPage('cards');
    }

    /**
     * Renders the carousel component reference page.
     *
     * Responsibility: Renders the carousel component reference page.
     */
    public function carousel(): Response
    {
        return $this->renderBaseUiPage('carousel');
    }

    /**
     * Renders the collapse component reference page.
     *
     * Responsibility: Renders the collapse component reference page.
     */
    public function collapse(): Response
    {
        return $this->renderBaseUiPage('collapse');
    }

    /**
     * Renders the colors reference page.
     *
     * Responsibility: Renders the colors reference page.
     */
    public function colors(): Response
    {
        return $this->renderBaseUiPage('colors');
    }

    /**
     * Renders the dropdowns component reference page.
     *
     * Responsibility: Renders the dropdowns component reference page.
     */
    public function dropdowns(): Response
    {
        return $this->renderBaseUiPage('dropdowns');
    }

    /**
     * Renders the grid-options reference page.
     *
     * Responsibility: Renders the grid-options reference page.
     */
    public function gridOptions(): Response
    {
        return $this->renderBaseUiPage('grid-options');
    }

    /**
     * Renders the images reference page.
     *
     * Responsibility: Renders the images reference page.
     */
    public function images(): Response
    {
        return $this->renderBaseUiPage('images');
    }

    /**
     * Renders the links reference page.
     *
     * Responsibility: Renders the links reference page.
     */
    public function links(): Response
    {
        return $this->renderBaseUiPage('links');
    }

    /**
     * Renders the list-group component reference page.
     *
     * Responsibility: Renders the list-group component reference page.
     */
    public function listGroup(): Response
    {
        return $this->renderBaseUiPage('list-group');
    }

    /**
     * Renders the modals component reference page.
     *
     * Responsibility: Renders the modals component reference page.
     */
    public function modals(): Response
    {
        return $this->renderBaseUiPage('modals');
    }

    /**
     * Renders the notifications component reference page.
     *
     * Responsibility: Renders the notifications component reference page.
     */
    public function notifications(): Response
    {
        return $this->renderBaseUiPage('notifications');
    }

    /**
     * Renders the offcanvas component reference page.
     *
     * Responsibility: Renders the offcanvas component reference page.
     */
    public function offcanvas(): Response
    {
        return $this->renderBaseUiPage('offcanvas');
    }

    /**
     * Renders the pagination component reference page.
     *
     * Responsibility: Renders the pagination component reference page.
     */
    public function pagination(): Response
    {
        return $this->renderBaseUiPage('pagination');
    }

    /**
     * Renders the placeholders component reference page.
     *
     * Responsibility: Renders the placeholders component reference page.
     */
    public function placeholders(): Response
    {
        return $this->renderBaseUiPage('placeholders');
    }

    /**
     * Renders the progress component reference page.
     *
     * Responsibility: Renders the progress component reference page.
     */
    public function progress(): Response
    {
        return $this->renderBaseUiPage('progress');
    }

    /**
     * Renders the popovers component reference page.
     *
     * Responsibility: Renders the popovers component reference page.
     */
    public function popovers(): Response
    {
        return $this->renderBaseUiPage('popovers');
    }

    /**
     * Renders the scrollspy component reference page.
     *
     * Responsibility: Renders the scrollspy component reference page.
     */
    public function scrollspy(): Response
    {
        return $this->renderBaseUiPage('scrollspy');
    }

    /**
     * Renders the spinners component reference page.
     *
     * Responsibility: Renders the spinners component reference page.
     */
    public function spinners(): Response
    {
        return $this->renderBaseUiPage('spinners');
    }

    /**
     * Renders the tabs component reference page.
     *
     * Responsibility: Renders the tabs component reference page.
     */
    public function tabs(): Response
    {
        return $this->renderBaseUiPage('tabs');
    }

    /**
     * Renders the tooltips component reference page.
     *
     * Responsibility: Renders the tooltips component reference page.
     */
    public function tooltips(): Response
    {
        return $this->renderBaseUiPage('tooltips');
    }

    /**
     * Renders the typography reference page.
     *
     * Responsibility: Renders the typography reference page.
     */
    public function typography(): Response
    {
        return $this->renderBaseUiPage('typography');
    }

    /**
     * Renders the utilities reference page.
     *
     * Responsibility: Renders the utilities reference page.
     */
    public function utilities(): Response
    {
        return $this->renderBaseUiPage('utilities');
    }

    /**
     * Renders the videos reference page.
     *
     * Responsibility: Renders the videos reference page.
     */
    public function videos(): Response
    {
        return $this->renderBaseUiPage('videos');
    }

    /**
     * Renders a chart reference page selected by family and page slug.
     *
     * Responsibility: Renders a chart reference page selected by family and page slug.
     */
    public function charts(string $family, string $page): Response
    {
        return $this->renderChartPage($family, $page);
    }

    /**
     * Renders a table reference page selected by page slug.
     *
     * Responsibility: Renders a table reference page selected by page slug.
     */
    public function table(string $page): Response
    {
        return $this->renderTablePage($page);
    }

    /**
     * Renders a DataTables reference page selected by page slug.
     *
     * Responsibility: Renders a DataTables reference page selected by page slug.
     */
    public function dataTable(string $page): Response
    {
        return $this->renderDataTablePage($page);
    }

    /**
     * Renders a base UI component page from its catalog slug.
     *
     * Responsibility: Renders a base UI component page from its catalog slug.
     */
    private function renderBaseUiPage(string $pageSlug): Response
    {
        return $this->renderMappedPage(self::BASE_UI_PAGES, 'base-ui', $pageSlug);
    }

    /**
     * Renders a form reference page from its catalog slug.
     *
     * Responsibility: Renders a form reference page from its catalog slug.
     */
    private function renderFormPage(string $pageSlug): Response
    {
        return $this->renderMappedPage(self::FORM_PAGES, 'forms', $pageSlug);
    }

    /**
     * Renders a chart reference page from its catalog identifiers.
     *
     * Responsibility: Renders a chart reference page from its catalog identifiers.
     */
    private function renderChartPage(string $family, string $page): Response
    {
        return $this->renderMappedPage(self::CHART_PAGES, 'charts', 'charts-' . $family . '-' . $page);
    }

    /**
     * Renders a table reference page from its catalog slug.
     *
     * Responsibility: Renders a table reference page from its catalog slug.
     */
    private function renderTablePage(string $pageSlug): Response
    {
        return $this->renderMappedPage(self::TABLE_PAGES, 'tables', $pageSlug);
    }

    /**
     * Renders a DataTables page through the shared table resolver.
     *
     * Responsibility: Renders a DataTables page through the shared table resolver.
     */
    private function renderDataTablePage(string $page): Response
    {
        return $this->renderTablePage('datatables-' . $page);
    }

    /**
     * Resolves and renders a page entry from the supplied catalog.
     *
     * Responsibility: Resolves and renders a page entry from the supplied catalog.
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
     * Renders the Demo UI shell with navigation and trusted preview HTML.
     *
     * Responsibility: Renders the Demo UI shell with navigation and trusted preview HTML.
     */
    private function renderPage(string $selectedFile, string $selectedSection, string $selectedLabel, ?string $pageSlug = null): Response
    {
        $appearanceRuntime = PlatformAppearanceManager::getInstance()->runtimeViewModel();
        $initialTheme = is_array($appearanceRuntime['lockedConfig'] ?? null)
            ? $appearanceRuntime['lockedConfig']
            : [];
        $sections = $this->demoUiSections();
        $pageStyles = $this->resolvePageStyles($pageSlug);

        $authUser = AuthManager::getInstance()->user() ?? [];

        return $this->view('demoui.demo-ui', [
            'title' => 'Demo UI',
            'document_title' => 'Demo UI - Catalyst',
            'pageTitle' => 'Demo UI',
            'csp_nonce' => CspNonce::get(),
            'auth_name' => trim((string) ($authUser['name'] ?? '')),
            'lang' => 'en',
            'direction' => 'ltr',
            'html_direction' => (string) ($initialTheme['dir'] ?? 'ltr'),
            'html_theme' => (string) ($initialTheme['theme'] ?? 'light'),
            'html_skin' => (string) ($initialTheme['skin'] ?? 'default'),
            'html_layout_width' => (string) ($initialTheme['width'] ?? 'fluid'),
            'html_layout_position' => (string) ($initialTheme['position'] ?? 'fixed'),
            'html_menu_color' => (string) ($initialTheme['sidenav-color'] ?? 'dark'),
            'html_sidenav_size' => (string) ($initialTheme['sidenav-size'] ?? 'default'),
            'html_topbar_color' => (string) ($initialTheme['topbar-color'] ?? 'gray'),
            'body_class' => 'catalyst-shell-body',
            'surface_context' => 'demo-ui',
            'surface_page' => $pageSlug ?? '',
            'inspinia_document' => $selectedFile,
            'show_topbar' => true,
            'show_sidebar' => true,
            'show_status_bar' => true,
            'show_theme_customizer' => true,
            'shell_class' => 'wrapper',
            'topbar_class' => 'app-topbar',
            'sidebar_class' => 'sidenav-menu',
            'sidebar_label' => 'Demo UI navigation',
            'content_class' => 'content-page',
            'status_bar_class' => 'catalyst-status-bar',
            'status_bar_label' => 'Catalyst Demo UI',
            'status_bar_context' => 'demo-ui',
            'brand_home_href' => '/demo-ui',
            'account_href' => '/dashboard',
            'account_label' => 'Account',
            'auth_avatar_src' => '/assets/vendor/inspinia/images/users/user-1.jpg',
            'platform_appearance_json' => TrustedHtml::fromString(InlineJson::encode($appearanceRuntime)),
            'styles' => $pageStyles,
            'navigation_model' => 'demo-ui',
            'navigation_model_data' => [
                'selected_file' => $selectedFile,
                'selected_section' => $selectedSection,
                'sections' => $sections,
                'chart_families' => self::CHART_FAMILIES,
                'chart_pages' => self::CHART_PAGES,
                'table_families' => self::TABLE_FAMILIES,
                'table_pages' => self::TABLE_PAGES,
                'catalogs' => array_merge(
                    self::BASE_UI_PAGES,
                    self::FORM_PAGES,
                    self::CHART_PAGES,
                    self::TABLE_PAGES
                ),
            ],
            'selected_doc_file' => $selectedFile,
            'selected_doc_label' => $selectedLabel,
            'selected_doc_section' => $selectedSection,
            'selected_doc_source_url' => self::THEME_BASE_URL . $selectedFile,
            'demo_ui_page_slug' => $pageSlug ?? '',
            'preview_html' => TrustedHtml::fromString($this->loadThemePreviewHtml($selectedFile)),
        ])->setAttribute('csp_profile', 'trusted-renderer');
    }

    /**
     * Flattens chart catalog entries for the Demo UI section list.
     *
     * Responsibility: Flattens chart catalog entries for the Demo UI section list.
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
     * Flattens table catalog entries for the Demo UI section list.
     *
     * Responsibility: Flattens table catalog entries for the Demo UI section list.
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
     * Loads trusted generated preview HTML for a Demo UI document.
     *
     * Responsibility: Loads trusted generated preview HTML for a Demo UI document.
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
     * Resolves a catalog file name to its page slug.
     *
     * Responsibility: Resolves a catalog file name to its page slug.
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
     * Returns additional stylesheet URLs required by a catalog page.
     *
     * Responsibility: Returns additional stylesheet URLs required by a catalog page.
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
