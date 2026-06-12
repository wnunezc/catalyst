<?php

declare(strict_types=1);

use Catalyst\Framework\Presence\RecordPresenceViewModel;

return static fn (array $scope): array => RecordPresenceViewModel::build($scope);
