<?php

namespace App\Domain\ValueObject;

class Currency
{
    private string $code;

    public function __construct(string $code)
    {
        if (!in_array($code, ['USD', 'EUR', 'PLN'])) {
            throw new \InvalidArgumentException('Invalid currency code');
        }
        $this->code = $code;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function equals(Currency $other): bool
    {
        return $this->code === $other->getCode();
    }
}
