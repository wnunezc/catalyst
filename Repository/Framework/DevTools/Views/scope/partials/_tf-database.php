<?php

declare(strict_types=1);

use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    return [
        'db_reset_confirm' => '¿Seguro? Se borrarán TODOS los datos y se recrearán las tablas.',
        'db_reset_csrf_field' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
    ];
};
