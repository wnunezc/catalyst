<?php

declare(strict_types=1);

use Catalyst\Framework\View\TrustedHtml;
use Catalyst\Helpers\Security\CsrfProtection;

return static function (array $scope): array {
    return [
        'csrfField' => TrustedHtml::fromString(CsrfProtection::getInstance()->getTokenField()),
    ];
};
