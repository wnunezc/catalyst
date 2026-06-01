<?php

declare(strict_types=1);

namespace Catalyst\Framework\Idempotency;

use RuntimeException;

final class IdempotencyConflictException extends RuntimeException
{
}
