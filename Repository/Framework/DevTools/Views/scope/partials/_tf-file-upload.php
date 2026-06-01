<?php

declare(strict_types=1);

use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    return [
        'upload_csrf_field' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
    ];
};
