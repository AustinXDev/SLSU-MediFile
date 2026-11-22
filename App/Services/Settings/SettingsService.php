<?php

namespace App\Services\Settings;

use App\Models\Admin;
use App\Repositories\AdminRepository;
use App\Repositories\SettingsRepository;
use App\Services\Logs\LogsService;
use App\Session\SessionManager;
use InvalidArgumentException;
use RuntimeException;

class SettingsService
{
    private const LOGO_MAX_BYTES = 2_000_000;
    private const LOGO_MIME_TYPES = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
    ];

    public function __construct(
        private SettingsRepository $settings,
        private AdminRepository $admins,
        private LogsService $logs,
        private SessionManager $session
    ) {
    }

    public function profile(): array
    {
        $adminId = $this->authenticatedAdminId();
        return $this->settings->getProfile($adminId)
            ?? throw new RuntimeException('Unable to load your account profile.');
    }

    public function updateProfile(array $input): array
    {
        $adminId = $this->authenticatedAdminId();
        $current = $this->admins->findById($adminId)
            ?? throw new RuntimeException('Unable to load your account profile.');

        $firstName = trim((string) ($input['first_name'] ?? ''));
        $lastName = trim((string) ($input['last_name'] ?? ''));
        $username = trim((string) ($input['username'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));

        if ($firstName === '' || mb_strlen($firstName, 'UTF-8') > 100) {
            throw new InvalidArgumentException('First name is required and must not exceed 100 characters.');
        }
        if ($lastName === '' || mb_strlen($lastName, 'UTF-8') > 100) {
            throw new InvalidArgumentException('Last name is required and must not exceed 100 characters.');
        }
        if ($username === '' || mb_strlen($username, 'UTF-8') > 50) {
            throw new InvalidArgumentException('Username is required and must not exceed 50 characters.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email, 'UTF-8') > 150) {
            throw new InvalidArgumentException('Please provide a valid email address.');
        }
        if ($username !== $current->username && $this->admins->usernameExists($username, $adminId)) {
            throw new InvalidArgumentException('Username is already in use.');
        }
        if ($email !== $current->email && $this->admins->emailExists($email, $adminId)) {
            throw new InvalidArgumentException('Email address is already in use.');
        }

        if (!$this->settings->updateProfile($adminId, $firstName, $lastName, $username, $email)) {
            throw new RuntimeException('Unable to save profile changes.');
        }

        $this->session->set('admin_username', $username);
        $this->logs->record(
            $adminId,
            'UPDATE PROFILE',
            'Updated their account profile information.',
            'settings',
            $adminId
        );

        return $this->profile();
    }

    public function changePassword(array $input): void
    {
        $adminId = $this->authenticatedAdminId();
        $admin = $this->admins->findById($adminId)
            ?? throw new RuntimeException('Unable to load your account.');
        $currentPassword = (string) ($input['current_password'] ?? '');
        $newPassword = (string) ($input['new_password'] ?? '');

        if ($currentPassword === '' || !password_verify($currentPassword, $admin->passwordHash)) {
            throw new InvalidArgumentException('Your current password is incorrect.');
        }
        $this->assertStrongPassword($newPassword);

        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        if ($passwordHash === false || !$this->settings->updatePassword($adminId, $passwordHash)) {
            throw new RuntimeException('Unable to update your password.');
        }

        $this->logs->record(
            $adminId,
            'CHANGE PASSWORD',
            'Changed their account password.',
            'settings',
            $adminId
        );
    }

    public function systemSettings(): array
    {
        $this->requireSuperAdmin();
        return $this->settings->getSystemSettings();
    }

    public function updateSystemSettings(array $input): array
    {
        $adminId = $this->requireSuperAdmin();
        $applicationName = trim((string) ($input['application_name'] ?? ''));
        $institutionName = trim((string) ($input['institution_name'] ?? ''));
        $contactEmail = trim((string) ($input['developer_contact_email'] ?? ''));

        if ($applicationName === '' || mb_strlen($applicationName, 'UTF-8') > 150) {
            throw new InvalidArgumentException('Application name is required and must not exceed 150 characters.');
        }
        if ($institutionName === '' || mb_strlen($institutionName, 'UTF-8') > 200) {
            throw new InvalidArgumentException('Institution name is required and must not exceed 200 characters.');
        }
        if ($contactEmail !== '' && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Please provide a valid developer contact email.');
        }

        $this->settings->saveSystemSettings([
            'application_name' => $applicationName,
            'institution_name' => $institutionName,
            'developer_contact_email' => $contactEmail,
        ], $adminId);
        $this->logs->record(
            $adminId,
            'UPDATE APPLICATION SETTINGS',
            'Updated system application settings.',
            'settings',
            $adminId
        );

        return $this->settings->getSystemSettings();
    }

    public function updateSchoolLogo(array $file): array
    {
        $adminId = $this->requireSuperAdmin();
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Select a valid image file to upload.');
        }
        if (
            !isset($file['tmp_name'], $file['size'])
            || !is_uploaded_file($file['tmp_name'])
            || $file['size'] < 1
            || $file['size'] > self::LOGO_MAX_BYTES
        ) {
            throw new InvalidArgumentException('The logo must be an image no larger than 2 MB.');
        }

        $image = getimagesize($file['tmp_name']);
        $mime = is_array($image) ? ($image['mime'] ?? '') : '';
        if (!$image || !isset(self::LOGO_MIME_TYPES[$mime])) {
            throw new InvalidArgumentException('Upload a PNG, JPEG, or WebP image.');
        }

        $directory = ROOT_PATH . '/public/assets/uploads/system';
        if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to prepare logo storage.');
        }

        $filename = 'school-logo-' . bin2hex(random_bytes(12)) . '.' . self::LOGO_MIME_TYPES[$mime];
        $target = $directory . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            throw new RuntimeException('Unable to save the uploaded logo.');
        }

        try {
            $relativePath = 'assets/uploads/system/' . $filename;
            $this->settings->saveSystemSettings(['school_logo_path' => $relativePath], $adminId);
        } catch (\Throwable $e) {
            if (is_file($target)) {
                unlink($target);
            }
            throw $e;
        }

        $this->logs->record(
            $adminId,
            'UPDATE SCHOOL LOGO',
            'Updated the application school logo.',
            'settings',
            $adminId
        );

        return $this->settings->getSystemSettings();
    }

    private function authenticatedAdminId(): int
    {
        $id = (int) $this->session->get('admin_id', 0);
        if (!$this->session->get('admin_authenticated') || $id <= 0) {
            throw new RuntimeException('Authentication is required.', 401);
        }

        return $id;
    }

    private function requireSuperAdmin(): int
    {
        $adminId = $this->authenticatedAdminId();
        if ($this->session->get('role') !== 'Super Admin') {
            throw new RuntimeException('Access denied.', 403);
        }

        return $adminId;
    }

    private function assertStrongPassword(string $password): void
    {
        if (mb_strlen($password, 'UTF-8') < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters long.');
        }

        foreach ([
            ['/[A-Z]/', 'Password must contain at least one uppercase letter.'],
            ['/[a-z]/', 'Password must contain at least one lowercase letter.'],
            ['/[0-9]/', 'Password must contain at least one number.'],
            ['/[!@#$%^&*]/', 'Password must contain at least one special character.'],
        ] as [$pattern, $message]) {
            if (!preg_match($pattern, $password)) {
                throw new InvalidArgumentException($message);
            }
        }
    }
}
