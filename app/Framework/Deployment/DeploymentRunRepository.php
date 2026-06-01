<?php

declare(strict_types=1);

namespace Catalyst\Framework\Deployment;

use Catalyst\Entities\DeploymentRun;
use Catalyst\Framework\Database\DatabaseManager;
use Catalyst\Framework\Traits\SingletonTrait;
use Catalyst\Helpers\Log\Logger;

final class DeploymentRunRepository
{
    use SingletonTrait;

    private DatabaseManager $db;
    private Logger $logger;

    protected function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function search(array $filters): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 15)));
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));

        $where = [];
        $bindings = [];

        if ($search !== '') {
            $where[] = '(profile_key LIKE ? OR release_id LIKE ? OR artifact_path LIKE ?)';
            $bindings[] = '%' . $search . '%';
            $bindings[] = '%' . $search . '%';
            $bindings[] = '%' . $search . '%';
        }

        if ($status !== '') {
            $where[] = 'status = ?';
            $bindings[] = $status;
        }

        $whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);

        try {
            $countRow = $this->db->connection()->selectOne(
                'SELECT COUNT(*) AS aggregate FROM deployment_runs' . $whereSql,
                $bindings
            ) ?: ['aggregate' => 0];
            $rows = $this->db->connection()->select(
                'SELECT * FROM deployment_runs' . $whereSql . ' ORDER BY started_at DESC LIMIT ' . $perPage . ' OFFSET ' . (($page - 1) * $perPage),
                $bindings
            ) ?: [];
        } catch (\Throwable $e) {
            $this->logger->warning('DeploymentRunRepository::search failed', ['error' => $e->getMessage()]);
            $countRow = ['aggregate' => 0];
            $rows = [];
        }

        return [
            'rows' => $rows,
            'total' => (int) ($countRow['aggregate'] ?? 0),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        try {
            return $this->db->connection()->select(
                'SELECT * FROM deployment_runs ORDER BY started_at DESC'
            ) ?: [];
        } catch (\Throwable $e) {
            $this->logger->warning('DeploymentRunRepository::all failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function find(int $id): ?array
    {
        try {
            return $this->db->connection()->selectOne(
                'SELECT * FROM deployment_runs WHERE id = ?',
                [$id]
            ) ?: null;
        } catch (\Throwable $e) {
            $this->logger->warning('DeploymentRunRepository::find failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function findModel(int $id): ?DeploymentRun
    {
        $model = DeploymentRun::find($id);

        return $model instanceof DeploymentRun ? $model : null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): DeploymentRun
    {
        $model = new DeploymentRun($payload);
        $model->save();

        return $model;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(DeploymentRun $model, array $payload): DeploymentRun
    {
        $model->fill($payload);
        $model->save();

        return $model;
    }
}
