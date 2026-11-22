<?php

namespace App\Services\Settings;

use App\Repositories\BackupRepository;
use App\Services\Logs\LogsService;
use App\Session\SessionManager;
use PDO;
use RuntimeException;

class BackupService
{
    private string $storageDirectory;

    public function __construct(
        private PDO $pdo,
        private BackupRepository $backups,
        private LogsService $logs,
        private SessionManager $session
    ) {
        $configuredDirectory = trim((string) ($_ENV['BACKUP_STORAGE_PATH'] ?? ''));
        $this->storageDirectory = $configuredDirectory !== ''
            ? $configuredDirectory
            : dirname(ROOT_PATH, 2) . DIRECTORY_SEPARATOR . 'SLSU-MediFile-private' . DIRECTORY_SEPARATOR . 'backups';
        if (
            $configuredDirectory !== ''
            && !str_starts_with($configuredDirectory, '/')
            && !preg_match('/\A[A-Za-z]:[\\\\\/]/', $configuredDirectory)
        ) {
            throw new RuntimeException('Backup storage path must be absolute.');
        }
    }

    public function list(): array
    {
        $this->requireSuperAdmin();
        $this->assertStorageOutsideApplication();
        return $this->backups->list();
    }

    public function create(): array
    {
        $adminId = $this->requireSuperAdmin();
        $this->ensureStorageDirectory();
        $dumpUtility = $this->findDumpUtility();
        $filename = 'backup-' . date('Y-m-d-His') . '-' . bin2hex(random_bytes(4)) . '.sql.gz';
        $archivePath = $this->storageDirectory . DIRECTORY_SEPARATOR . $filename;
        $errorPath = tempnam($this->storageDirectory, '.backup-error-');
        if ($errorPath === false) {
            throw new RuntimeException('Unable to prepare the database backup process.');
        }
        $gzip = gzopen($archivePath, 'wb6');
        if ($gzip === false) {
            unlink($errorPath);
            throw new RuntimeException('Unable to create the backup archive.');
        }

        $environment = getenv();
        if (!is_array($environment)) {
            $environment = [];
        }
        $environment['MYSQL_PWD'] = (string) ($_ENV['DB_PASS'] ?? '');
        $command = [
            $dumpUtility,
            '--host=' . (string) $_ENV['DB_HOST'],
            '--port=' . (string) ($_ENV['DB_PORT'] ?? '3306'),
            '--user=' . (string) $_ENV['DB_USER'],
            '--single-transaction',
            '--routines',
            '--triggers',
            '--events',
            '--default-character-set=utf8mb4',
            '--databases',
            (string) $_ENV['DB_NAME'],
        ];
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['file', $errorPath, 'w'],
        ];

        $process = null;
        $pipes = [];
        try {
            $process = proc_open($command, $descriptors, $pipes, ROOT_PATH, $environment);
            if (!is_resource($process)) {
                throw new RuntimeException('Unable to start the database backup process.');
            }

            fclose($pipes[0]);
            unset($pipes[0]);
            while (!feof($pipes[1])) {
                $chunk = fread($pipes[1], 8192);
                if ($chunk === false) {
                    throw new RuntimeException('Unable to read database backup output.');
                }
                if ($chunk !== '' && gzwrite($gzip, $chunk) !== strlen($chunk)) {
                    throw new RuntimeException('Unable to write the backup archive.');
                }
            }
            fclose($pipes[1]);
            unset($pipes[1]);
            $exitCode = proc_close($process);
            $process = null;
            gzclose($gzip);
            $gzip = null;

            if ($exitCode !== 0 || !is_file($archivePath) || filesize($archivePath) === 0) {
                throw new RuntimeException('Database backup generation failed.');
            }

            $sizeBytes = (int) filesize($archivePath);
            $this->pdo->beginTransaction();
            try {
                $this->backups->create($filename, $sizeBytes, $adminId);
                $this->logs->record(
                    $adminId,
                    'CREATE BACKUP',
                    'Created a full database backup.',
                    'settings',
                    $adminId
                );
                $this->pdo->commit();
            } catch (\Throwable $e) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                throw $e;
            }

            return [
                'filename' => $filename,
                'size_bytes' => $sizeBytes,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 'Completed',
            ];
        } catch (\Throwable $e) {
            foreach ($pipes as $pipe) {
                if (is_resource($pipe)) {
                    fclose($pipe);
                }
            }
            if (is_resource($process)) {
                proc_terminate($process);
                proc_close($process);
            }
            if (is_resource($gzip)) {
                gzclose($gzip);
            }
            if (is_file($archivePath)) {
                unlink($archivePath);
            }
            throw $e;
        } finally {
            if (is_file($errorPath)) {
                unlink($errorPath);
            }
        }
    }

    public function download(int $backupId): array
    {
        $adminId = $this->requireSuperAdmin();
        $this->assertStorageOutsideApplication();
        $record = $this->backups->findById($backupId)
            ?? throw new RuntimeException('Backup archive not found.');
        $path = $this->pathFor((string) $record['filename']);
        if (!is_file($path)) {
            throw new RuntimeException('Backup archive file is missing.');
        }

        $this->logs->record(
            $adminId,
            'DOWNLOAD BACKUP',
            'Downloaded a database backup.',
            'settings',
            $backupId
        );

        return ['path' => $path, 'filename' => $record['filename']];
    }

    public function delete(int $backupId): void
    {
        $adminId = $this->requireSuperAdmin();
        $this->assertStorageOutsideApplication();
        $record = $this->backups->findById($backupId)
            ?? throw new RuntimeException('Backup archive not found.');
        $path = $this->pathFor((string) $record['filename']);

        $quarantinePath = null;
        $this->pdo->beginTransaction();
        try {
            if (is_file($path)) {
                $quarantinePath = $path . '.deleting-' . bin2hex(random_bytes(6));
                if (!rename($path, $quarantinePath)) {
                    throw new RuntimeException('Unable to prepare the backup file for deletion.');
                }
            }
            if (!$this->backups->delete($backupId)) {
                throw new RuntimeException('Unable to delete the backup record.');
            }
            $this->logs->record(
                $adminId,
                'DELETE BACKUP',
                'Deleted a database backup.',
                'settings',
                $backupId
            );
            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            if ($quarantinePath !== null && is_file($quarantinePath) && !rename($quarantinePath, $path)) {
                error_log('Failed to restore a private backup file after deletion rollback.');
            }
            throw $e;
        }

        if ($quarantinePath !== null && is_file($quarantinePath) && !unlink($quarantinePath)) {
            error_log('Deleted backup archive remains in private quarantine storage.');
        }
    }

    private function requireSuperAdmin(): int
    {
        $adminId = (int) $this->session->get('admin_id', 0);
        if (!$this->session->get('admin_authenticated') || $adminId <= 0) {
            throw new RuntimeException('Authentication is required.', 401);
        }
        if ($this->session->get('role') !== 'Super Admin') {
            throw new RuntimeException('Access denied.', 403);
        }

        return $adminId;
    }

    private function ensureStorageDirectory(): void
    {
        if (!is_dir($this->storageDirectory)) {
            if (!mkdir($this->storageDirectory, 0700, true) && !is_dir($this->storageDirectory)) {
                throw new RuntimeException('Unable to prepare private backup storage.');
            }
        }
        if (DIRECTORY_SEPARATOR !== '\\' && !chmod($this->storageDirectory, 0700)) {
            throw new RuntimeException('Unable to secure private backup storage permissions.');
        }

        $this->assertStorageOutsideApplication();
    }

    private function assertStorageOutsideApplication(): void
    {
        $projectRoot = realpath(ROOT_PATH);
        if ($projectRoot === false) {
            throw new RuntimeException('Unable to resolve application storage paths.');
        }

        $rootPrefix = strtolower(rtrim(str_replace('\\', '/', $projectRoot), '/') . '/');
        $configuredPath = strtolower(str_replace('\\', '/', $this->storageDirectory));
        if (str_starts_with($configuredPath, $rootPrefix)) {
            throw new RuntimeException('Backup storage must be outside the application web directory.');
        }

        $storagePath = realpath($this->storageDirectory);
        if ($storagePath !== false) {
            $resolvedPath = strtolower(rtrim(str_replace('\\', '/', $storagePath), '/') . '/');
            if (str_starts_with($resolvedPath, $rootPrefix)) {
                throw new RuntimeException('Backup storage must be outside the application web directory.');
            }
        }
    }

    private function findDumpUtility(): string
    {
        $configuredPath = trim((string) ($_ENV['MYSQLDUMP_PATH'] ?? ''));
        if ($configuredPath !== '' && is_file($configuredPath)) {
            return $configuredPath;
        }

        $candidates = [
            dirname(ROOT_PATH, 2) . '/mysql/bin/mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/bin/mariadb-dump',
        ];
        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException('Database dump utility is not configured.');
    }

    private function pathFor(string $filename): string
    {
        if (!preg_match('/\Abackup-\d{4}-\d{2}-\d{2}-\d{6}-[a-f0-9]{8}\.sql\.gz\z/', $filename)) {
            throw new RuntimeException('Invalid backup archive name.');
        }

        return $this->storageDirectory . DIRECTORY_SEPARATOR . $filename;
    }
}
