<?php

namespace App\Domain\Entity;

use App\Domain\ValueObject\Currency;
use App\Domain\ValueObject\Money;

class BankAccount
{
    private string $id;
    private Money $balance;
    private array $debitTransactionsToday = [];
    private const TRANSACTION_FEE_PERCENTAGE = 0.005; // 0.5%
    private const MAX_DAILY_DEBITS = 3;

    public function __construct(string $id, Currency $currency)
    {
        $this->id = $id;
        $this->balance = new Money(0.0, $currency);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getBalance(): Money
    {
        return $this->balance;
    }

    public function credit(Money $money): void
    {
        $this->assertSameCurrency($money);
        $this->balance = $this->balance->add($money);
    }

    public function debit(Money $money, \DateTimeInterface $date): void
    {
        $this->assertSameCurrency($money);
        $this->assertCanDebit($money, $date);

        $fee = $money->multiply(self::TRANSACTION_FEE_PERCENTAGE);
        $total = $money->add($fee);

        $this->balance = $this->balance->subtract($total);
        $this->recordDebitTransaction($date);
    }

    private function assertSameCurrency(Money $money): void
    {
        if (!$this->balance->getCurrency()->equals($money->getCurrency())) {
            throw new \DomainException('Currency mismatch');
        }
    }

    private function assertCanDebit(Money $money, \DateTimeInterface $date): void
    {
        $fee = $money->multiply(self::TRANSACTION_FEE_PERCENTAGE);
        $total = $money->add($fee);

        if ($this->balance->getAmount() < $total->getAmount()) {
            throw new \DomainException('Insufficient balance');
        }

        $today = $date->format('Y-m-d');
        $currentDebits = $this->debitTransactionsToday[$today] ?? 0;

        if ($currentDebits >= self::MAX_DAILY_DEBITS) {
            throw new \DomainException('Daily debit limit exceeded');
        }
    }

    private function recordDebitTransaction(\DateTimeInterface $date): void
    {
        $today = $date->format('Y-m-d');
        $this->debitTransactionsToday[$today] = ($this->debitTransactionsToday[$today] ?? 0) + 1;
    }
}
