<?php

declare(strict_types=1);

use Catalyst\Framework\DataGrid\DataGridViewModel;

return static fn (array $scope): array => DataGridViewModel::build($scope);
