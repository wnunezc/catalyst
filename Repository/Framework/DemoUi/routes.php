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

use Catalyst\Repository\DemoUi\Controllers\DemoUiController;
use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\View\View;

$router = Router::getInstance();
$moduleMiddleware = [AuthMiddleware::class];

View::getInstance()->addPath(
    'demoui',
    implode(DS, [PD, 'Repository', 'Framework', 'DemoUi', 'Views'])
);

$router->get('/demo-ui', [DemoUiController::class, 'index'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/basic-elements', [DemoUiController::class, 'basicElements'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/pickers', [DemoUiController::class, 'pickers'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/select', [DemoUiController::class, 'select'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/validation', [DemoUiController::class, 'validation'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/wizard', [DemoUiController::class, 'wizard'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/file-uploads', [DemoUiController::class, 'fileUploads'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/text-editors', [DemoUiController::class, 'textEditors'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/range-slider', [DemoUiController::class, 'rangeSlider'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/charts/{family}/{page}', [DemoUiController::class, 'charts'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/tables/datatables/{page}', [DemoUiController::class, 'dataTable'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/tables/{page}', [DemoUiController::class, 'table'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/alerts', [DemoUiController::class, 'alerts'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/accordions', [DemoUiController::class, 'accordions'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/badges', [DemoUiController::class, 'badges'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/breadcrumb', [DemoUiController::class, 'breadcrumb'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/buttons', [DemoUiController::class, 'buttons'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/cards', [DemoUiController::class, 'cards'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/carousel', [DemoUiController::class, 'carousel'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/collapse', [DemoUiController::class, 'collapse'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/colors', [DemoUiController::class, 'colors'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/dropdowns', [DemoUiController::class, 'dropdowns'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/grid-options', [DemoUiController::class, 'gridOptions'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/images', [DemoUiController::class, 'images'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/links', [DemoUiController::class, 'links'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/list-group', [DemoUiController::class, 'listGroup'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/modals', [DemoUiController::class, 'modals'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/notifications', [DemoUiController::class, 'notifications'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/offcanvas', [DemoUiController::class, 'offcanvas'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/pagination', [DemoUiController::class, 'pagination'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/placeholders', [DemoUiController::class, 'placeholders'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/popovers', [DemoUiController::class, 'popovers'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/progress', [DemoUiController::class, 'progress'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/scrollspy', [DemoUiController::class, 'scrollspy'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/spinners', [DemoUiController::class, 'spinners'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/tabs', [DemoUiController::class, 'tabs'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/tooltips', [DemoUiController::class, 'tooltips'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/typography', [DemoUiController::class, 'typography'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/utilities', [DemoUiController::class, 'utilities'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/videos', [DemoUiController::class, 'videos'])
       ->middleware($moduleMiddleware);
