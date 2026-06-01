<?php

declare(strict_types=1);

use Catalyst\Repository\MigrationUi\Controllers\MigrationUiController;
use Catalyst\Framework\Middleware\AuthMiddleware;
use Catalyst\Framework\Route\Router;
use Catalyst\Framework\View\View;

$router = Router::getInstance();
$moduleMiddleware = [AuthMiddleware::class];

View::getInstance()->addPath(
    'migrationui',
    implode(DS, [PD, 'Repository', 'Framework', 'MigrationUi', 'Views'])
);

$router->get('/demo-ui', [MigrationUiController::class, 'index'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/basic-elements', [MigrationUiController::class, 'basicElements'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/pickers', [MigrationUiController::class, 'pickers'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/select', [MigrationUiController::class, 'select'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/validation', [MigrationUiController::class, 'validation'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/wizard', [MigrationUiController::class, 'wizard'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/file-uploads', [MigrationUiController::class, 'fileUploads'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/text-editors', [MigrationUiController::class, 'textEditors'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/range-slider', [MigrationUiController::class, 'rangeSlider'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/charts/{family}/{page}', [MigrationUiController::class, 'charts'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/tables/datatables/{page}', [MigrationUiController::class, 'dataTable'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/tables/{page}', [MigrationUiController::class, 'table'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/alerts', [MigrationUiController::class, 'alerts'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/accordions', [MigrationUiController::class, 'accordions'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/badges', [MigrationUiController::class, 'badges'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/breadcrumb', [MigrationUiController::class, 'breadcrumb'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/buttons', [MigrationUiController::class, 'buttons'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/cards', [MigrationUiController::class, 'cards'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/carousel', [MigrationUiController::class, 'carousel'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/collapse', [MigrationUiController::class, 'collapse'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/colors', [MigrationUiController::class, 'colors'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/dropdowns', [MigrationUiController::class, 'dropdowns'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/grid-options', [MigrationUiController::class, 'gridOptions'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/images', [MigrationUiController::class, 'images'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/links', [MigrationUiController::class, 'links'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/list-group', [MigrationUiController::class, 'listGroup'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/modals', [MigrationUiController::class, 'modals'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/notifications', [MigrationUiController::class, 'notifications'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/offcanvas', [MigrationUiController::class, 'offcanvas'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/pagination', [MigrationUiController::class, 'pagination'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/placeholders', [MigrationUiController::class, 'placeholders'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/popovers', [MigrationUiController::class, 'popovers'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/progress', [MigrationUiController::class, 'progress'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/scrollspy', [MigrationUiController::class, 'scrollspy'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/spinners', [MigrationUiController::class, 'spinners'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/tabs', [MigrationUiController::class, 'tabs'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/tooltips', [MigrationUiController::class, 'tooltips'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/typography', [MigrationUiController::class, 'typography'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/utilities', [MigrationUiController::class, 'utilities'])
       ->middleware($moduleMiddleware);

$router->get('/demo-ui/videos', [MigrationUiController::class, 'videos'])
       ->middleware($moduleMiddleware);
