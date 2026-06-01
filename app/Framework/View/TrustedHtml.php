<?php

declare(strict_types=1);

namespace Catalyst\Framework\View;

final readonly class TrustedHtml
{
    public function __construct(
        private string $html
    ) {
    }

    public static function fromString(string $html): self
    {
        return new self($html);
    }

    public function toHtml(): string
    {
        return $this->html;
    }

    public function __toString(): string
    {
        return $this->html;
    }
}
