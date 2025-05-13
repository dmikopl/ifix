<?php

namespace App\Application\Service;

use App\Domain\Entity\BankAccount;
use App\Domain\Repository\BankAccountRepositoryInterface;
use App\Domain\ValueObject\Currency;
use App\Domain\ValueObject\Money;

class BankAccountService
{
    private BankAccountRepositoryInterface $repository;

    public function __construct(BankAccountRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function createAccount(string $id, string $currencyCode): BankAccount
    {
        $account = new BankAccount($id, new Currency($currencyCode));
        $this->repository->save($account);
        return $account;
    }

    public function credit(string $accountId, float $amount, string $currencyCode): void
    {
        $account = $this->getAccount($accountId);
        $money = new Money($amount, new Currency($currencyCode));
        $account->credit($money);
        $this->repository->save($account);
    }

    public function debit(string $accountId, float $amount, string $currencyCode, \DateTimeInterface $date): void
    {
        $account = $this->getAccount($accountId);
        $money = new Money($amount, new Currency($currencyCode));
        $account->debit($money, $date);
        $this->repository->save($account);
    }

    private function getAccount(string $accountId): BankAccount
    {
        $account = $this->repository->findById($accountId);
        if (!$account) {
            throw new \InvalidArgumentException('Account not found');
        }
        return $account;
    }
}
