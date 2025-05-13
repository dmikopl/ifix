<?php

namespace App\Tests\Domain\Entity;

use App\Domain\Entity\BankAccount;
use App\Domain\ValueObject\Currency;
use App\Domain\ValueObject\Money;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \App\Domain\Entity\BankAccount
 */
class BankAccountTest extends TestCase
{
    private Currency $currency;
    private BankAccount $account;

    protected function setUp(): void
    {
        $this->currency = new Currency('USD');
        $this->account = new BankAccount('123', $this->currency);
    }

    /**
     * @covers ::getBalance
     */
    public function testInitialBalanceIsZero(): void
    {
        $this->assertEquals(0.0, $this->account->getBalance()->getAmount());
    }

    /**
     * @covers ::credit
     * @covers ::getBalance
     */
    public function testCreditIncreasesBalance(): void
    {
        $money = new Money(100.0, $this->currency);
        $this->account->credit($money);
        $this->assertEquals(100.0, $this->account->getBalance()->getAmount());
    }

    /**
     * @covers ::credit
     * @covers ::debit
     * @covers ::getBalance
     */
    public function testDebitDecreasesBalanceWithFee(): void
    {
        $this->account->credit(new Money(1000.0, $this->currency));
        $this->account->debit(new Money(100.0, $this->currency), new \DateTime());
        $expectedBalance = 1000.0 - (100.0 + 100.0 * 0.005); // Kwota + 0.5% opłaty
        $this->assertEquals($expectedBalance, $this->account->getBalance()->getAmount());
    }

    /**
     * @covers ::debit
     */
    public function testDebitFailsIfInsufficientBalance(): void
    {
        $this->expectException(\DomainException::class);
        $this->account->debit(new Money(100.0, $this->currency), new \DateTime());
    }

    /**
     * @covers ::credit
     * @covers ::debit
     */
    public function testDebitFailsIfCurrencyMismatch(): void
    {
        $this->account->credit(new Money(1000.0, $this->currency));
        $this->expectException(\DomainException::class);
        $this->account->debit(new Money(100.0, new Currency('EUR')), new \DateTime());
    }

    /**
     * @covers ::credit
     * @covers ::debit
     */
    public function testDebitFailsIfDailyLimitExceeded(): void
    {
        $this->account->credit(new Money(10000.0, $this->currency));
        $date = new \DateTime();
        $money = new Money(100.0, $this->currency);

        $this->account->debit($money, $date);
        $this->account->debit($money, $date);
        $this->account->debit($money, $date);

        $this->expectException(\DomainException::class);
        $this->account->debit($money, $date);
    }

    /**
     * @covers ::credit
     */
    public function testCreditFailsIfCurrencyMismatch(): void
    {
        $this->expectException(\DomainException::class);
        $this->account->credit(new Money(100.0, new Currency('EUR')));
    }
}
