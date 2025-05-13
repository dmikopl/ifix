<?php

namespace App\Domain\Repository;

use App\Domain\Entity\BankAccount;

interface BankAccountRepositoryInterface
{
    public function findById(string $id): ?BankAccount;
    public function save(BankAccount $account): void;
}
