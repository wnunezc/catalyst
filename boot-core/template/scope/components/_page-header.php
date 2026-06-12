<?php

declare(strict_types=1);

use Catalyst\Framework\View\PageHeaderViewModel;

return static fn (array $scope): array => PageHeaderViewModel::build($scope);
