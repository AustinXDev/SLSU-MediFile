<?php

namespace App\Controllers\Admin;

use App\Services\Admin\AdminService;
use RuntimeException;

class AdminController
{
    public function __construct(
        private AdminService $service
    ) {
    }

    public function getAccounts(array $query): array
    {
        return $this->service->listAccounts($query);
    }

    public function getAccount(int $id): array
    {
        $account = $this->service->getAccount($id);

        if (!$account) {
            throw new RuntimeException('User account not found.');
        }

        return [
            'account' => $account->toArray(),
        ];
    }

    public function create(array $input): array
    {
        return $this->service->createAccount($input);
    }

    public function update(array $input): array
    {
        $id = (int) ($input['id'] ?? 0);

        if ($id <= 0) {
            throw new RuntimeException('Invalid user account ID.');
        }

        return $this->service->updateAccount($id, $input);
    }

    public function delete(int $id): array
    {
        return $this->service->deleteAccount($id);
    }

    public function resetPassword(array $input): array
    {
        $id = (int) ($input['id'] ?? 0);
        $password = (string) ($input['password'] ?? '');

        return $this->service->resetPassword($id, $password);
    }

    public function toggleStatus(array $input): array
    {
        $id = (int) ($input['id'] ?? 0);
        $status = trim((string) ($input['status'] ?? ''));

        return $this->service->toggleStatus($id, $status);
    }
}
