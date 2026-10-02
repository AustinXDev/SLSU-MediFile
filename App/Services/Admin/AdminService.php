<?php

namespace App\Services\Admin;

use App\Models\Admin;
use App\Repositories\AdminRepository;
use RuntimeException;

class AdminService
{
    public function __construct(
        private AdminRepository $adminRepository
    ) {
    }

    /**
     * Gets a paginated list of user accounts based on the provided query parameters.
     */
    public function listAccounts(array $query): array
    {
        $page = max(1, (int) ($query['page'] ?? 1));
        $limit = max(1, min(25, (int) ($query['limit'] ?? 10)));
        $search = trim((string) ($query['search'] ?? ''));
        $role = trim((string) ($query['role'] ?? ''));
        $status = trim((string) ($query['status'] ?? ''));

        $roles = $this->adminRepository->getSupportedRoles();
        $statuses = $this->adminRepository->getSupportedStatuses();

        if ($role !== '' && !in_array($role, $roles, true)) {
            throw new RuntimeException('Selected role is not supported.');
        }

        if ($status !== '' && !in_array($status, $statuses, true)) {
            throw new RuntimeException('Selected status is not supported.');
        }

        $total = $this->adminRepository->countAccounts($search, $role, $status);
        $offset = ($page - 1) * $limit;
        $accounts = $this->adminRepository->listAccounts($search, $role, $status, $limit, $offset);
        $totalPages = max(1, (int) ceil($total / $limit));

        return [
            'accounts' => $accounts,
            'pagination' => [
                'page' => $page,
                'pageSize' => $limit,
                'total' => $total,
                'totalPages' => $totalPages,
            ],
            'roles' => $roles,
            'statuses' => $statuses,
        ];
    }


    /**
     * Gets a user account by its ID.
     */
    public function getAccount(int $id): ?Admin
    {
        if ($id <= 0) {
            throw new RuntimeException('Invalid user account ID.');
        }

        return $this->adminRepository->findById($id);
    }


    /**
     * Creates a new user account with the provided input data.
     */
    public function createAccount(array $input): array
    {
        $payload = $this->normalizeCreatePayload($input);

        if ($this->adminRepository->usernameExists($payload['username'])) {
            throw new RuntimeException('Username is already in use.');
        }

        if ($this->adminRepository->emailExists($payload['email'])) {
            throw new RuntimeException('Email address is already in use.');
        }

        $adminId = $this->adminRepository->create($payload);
        $account = $this->adminRepository->findById($adminId);

        if (!$account) {
            throw new RuntimeException('Unable to load the created user account.');
        }

        return [
            'status' => 'success',
            'message' => 'User account created successfully.',
            'account' => $account->toArray(),
        ];
    }

    /**
     * Updates an existing user account with the provided input data.
     */
    public function updateAccount(int $id, array $input): array
    {
        $payload = $this->normalizeUpdatePayload($input);

        $existing = $this->adminRepository->findById($id);
        if (!$existing) {
            throw new RuntimeException('User account not found.');
        }

        if (($payload['username'] ?? '') !== $existing->username && $this->adminRepository->usernameExists($payload['username'], $id)) {
            throw new RuntimeException('Username is already in use.');
        }

        if (($payload['email'] ?? '') !== $existing->email && $this->adminRepository->emailExists($payload['email'], $id)) {
            throw new RuntimeException('Email address is already in use.');
        }


        //Convert password to hash if provided in the payload
        if (isset($payload['password'])) {
            $payload['password_hash'] = password_hash($payload['password'], PASSWORD_DEFAULT);
            unset($payload['password']);
        }

        $updated = $this->adminRepository->update($id, $payload);
        if (!$updated) {
            throw new RuntimeException('Unable to update user account.');
        }

        $account = $this->adminRepository->findById($id);

        return [
            'status' => 'success',
            'message' => 'User account updated successfully.',
            'account' => $account?->toArray(),
        ];
    }

    /**
     * Deletes a user account by its ID.
     */
    public function deleteAccount(int $id): array
    {
        $id = (int) $id;
        if ($id <= 0) {
            throw new RuntimeException('Invalid user account ID.');
        }

        $account = $this->adminRepository->findById($id);
        if (!$account) {
            throw new RuntimeException('User account not found.');
        }

        $softDeleted = $this->adminRepository->Delete($id);
        if (!$softDeleted) {
            throw new RuntimeException('Unable to delete user account.');
        }

        return [
            'status' => 'success',
            'message' => 'User account deleted successfully.',
        ];
    }


    /**
     * Validates the strength of a password based on defined criteria.
     */
    private function validateStrongPassword(string $password): void
    {
        if (mb_strlen($password, 'UTF-8') < 8) {
            throw new RuntimeException('Password must be at least 8 characters long.');
        }

        $requirements = [
            ['/[A-Z]/', 'Password must contain at least one uppercase letter.'],
            ['/[a-z]/', 'Password must contain at least one lowercase letter.'],
            ['/[0-9]/', 'Password must contain at least one number.'],
            ['/[!@#$%^&*]/', 'Password must contain at least one special character.'],
        ];

        foreach ($requirements as [$pattern, $message]) {
            if (!preg_match($pattern, $password)) {
                throw new RuntimeException($message);
            }
        }
    }


    /**
     * Normalizes the input data for creating a new user account.
     */
    private function normalizeCreatePayload(array $input): array
    {
        $firstName = trim((string) ($input['first_name'] ?? $input['firstName'] ?? ''));
        $lastName = trim((string) ($input['last_name'] ?? $input['lastName'] ?? ''));
        $username = trim((string) ($input['username'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $role = trim((string) ($input['role'] ?? ''));
        $status = trim((string) ($input['status'] ?? 'Active'));
        $password = trim((string) ($input['password'] ?? ''));

        if ($firstName === '') {
            throw new RuntimeException('First name is required.');
        }

        if ($lastName === '') {
            throw new RuntimeException('Last name is required.');
        }

        if ($username === '') {
            throw new RuntimeException('Username is required.');
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Please provide a valid email address.');
        }

        $roles = $this->adminRepository->getSupportedRoles();
        if ($role === '' || !in_array($role, $roles, true)) {
            throw new RuntimeException('Please select a valid role.');
        }

        $statuses = $this->adminRepository->getSupportedStatuses();
        if ($status === '' || !in_array($status, $statuses, true)) {
            throw new RuntimeException('Please select a valid status.');
        }

        $this->validateStrongPassword($password);

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username' => $username,
            'email' => $email,
            'role' => $role,
            'status' => $status,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ];
    }


    /**
     * Normalizes the input data for updating an existing user account.
     */
    private function normalizeUpdatePayload(array $input): array
    {
        $firstName = trim((string) ($input['first_name'] ?? $input['firstName'] ?? ''));
        $lastName = trim((string) ($input['last_name'] ?? $input['lastName'] ?? ''));
        $username = trim((string) ($input['username'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $role = trim((string) ($input['role'] ?? ''));
        $status = trim((string) ($input['status'] ?? ''));
        $password = trim((string) ($input['password'] ?? ''));

        if ($firstName === '') {
            throw new RuntimeException('First name is required.');
        }

        if ($lastName === '') {
            throw new RuntimeException('Last name is required.');
        }

        if ($username === '') {
            throw new RuntimeException('Username is required.');
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Please provide a valid email address.');
        }

        $roles = $this->adminRepository->getSupportedRoles();
        if ($role === '' || !in_array($role, $roles, true)) {
            throw new RuntimeException('Please select a valid role.');
        }

        $statuses = $this->adminRepository->getSupportedStatuses();
        if ($status === '' || !in_array($status, $statuses, true)) {
            throw new RuntimeException('Please select a valid status.');
        }

        $payload = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username' => $username,
            'email' => $email,
            'role' => $role,
            'status' => $status,
        ];

        if ($password !== '') {
            $this->validateStrongPassword($password);

            $payload['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        return $payload;
    }
}
