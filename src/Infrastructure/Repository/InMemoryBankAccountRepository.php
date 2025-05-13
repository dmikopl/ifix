<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\BankAccount;
use App\Domain\Repository\BankAccountRepositoryInterface;

class InMemoryBankAccountRepository implements BankAccountRepositoryInterface
{
    private array $accounts = [];

    public function findById(string $id): ?BankAccount
    {
        return $this->accounts[$id] ?? null;
    }

    public function save(BankAccount $account): void
    {
        $this->accounts[$account->getId()] = $account;
    }
}
