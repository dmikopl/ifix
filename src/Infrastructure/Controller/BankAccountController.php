<?php

namespace App\Infrastructure\Controller;

use App\Application\Service\BankAccountService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BankAccountController extends AbstractController
{
    private BankAccountService $bankAccountService;

    public function __construct(BankAccountService $bankAccountService)
    {
        $this->bankAccountService = $bankAccountService;
    }

    #[Route('/account/create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $account = $this->bankAccountService->createAccount($data['id'], $data['currency']);
        return $this->json(['id' => $account->getId(), 'balance' => $account->getBalance()->getAmount()]);
    }

    #[Route('/account/credit', methods: ['POST'])]
    public function credit(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $this->bankAccountService->credit($data['accountId'], $data['amount'], $data['currency']);
        return $this->json(['status' => 'success']);
    }

    #[Route('/account/debit', methods: ['POST'])]
    public function debit(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $this->bankAccountService->debit($data['accountId'], $data['amount'], $data['currency'], new \DateTime());
        return $this->json(['status' => 'success']);
    }
}
