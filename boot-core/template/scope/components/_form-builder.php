<?php

declare(strict_types=1);

use Catalyst\Framework\Form\FormBuilderViewModel;

return static fn (array $scope): array => FormBuilderViewModel::build($scope);
