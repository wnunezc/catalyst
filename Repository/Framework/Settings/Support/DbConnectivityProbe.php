<?php

declare(strict_types=1);

namespace Catalyst\Repository\Settings\Support;

use PDO;
use PDOException;
use Throwable;

final class DbConnectivityProbe
{
    public function probe(
        string $host,
        int $port,
        string $database,
        string $username,
        string $password
    ): string {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $database);
            new PDO($dsn, $username, $password, $options);

            return 'ok';
        } catch (PDOException $exception) {
            if ((int) $exception->getCode() === 1049 || str_contains($exception->getMessage(), 'Unknown database')) {
                try {
                    $dsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $host, $port);
                    new PDO($dsn, $username, $password, $options);

                    return 'db_missing';
                } catch (Throwable) {
                    return 'unreachable';
                }
            }

            return 'unreachable';
        } catch (Throwable) {
            return 'unreachable';
        }
    }
}
