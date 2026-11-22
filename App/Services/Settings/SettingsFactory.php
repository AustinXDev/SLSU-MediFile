<?php

namespace App\Services\Settings;

use App\Repositories\AdminRepository;
use App\Repositories\BackupRepository;
use App\Repositories\ExportRepository;
use App\Repositories\LogRepositories\LogRepository;
use App\Repositories\SettingsRepository;
use App\Services\Logs\LogsService;
use App\Session\SessionManager;
use PDO;

final class SettingsFactory
{
    public static function settings(PDO $pdo, SessionManager $session): SettingsService
    {
        return new SettingsService(
            new SettingsRepository($pdo),
            new AdminRepository($pdo),
            new LogsService(new LogRepository($pdo)),
            $session
        );
    }

    public static function backups(PDO $pdo, SessionManager $session): BackupService
    {
        return new BackupService(
            $pdo,
            new BackupRepository($pdo),
            new LogsService(new LogRepository($pdo)),
            $session
        );
    }

    public static function exports(PDO $pdo, SessionManager $session): ExportService
    {
        return new ExportService(
            new ExportRepository($pdo),
            new LogsService(new LogRepository($pdo)),
            $session
        );
    }
}
