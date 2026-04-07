<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActionLog;
use Illuminate\Database\Eloquent\Model;

final class ActionLogService
{
    private const SENSITIVE_FIELDS = [
        'password',
        'password_confirmation',
        'remember_token',
        'token',
        'secret',
        'api_key',
        'access_token',
        'refresh_token',
    ];

    private const ENTITY_MODULE_MAP = [
        'manufacturer' => 'manufacturers',
        'product' => 'products',
        'batch' => 'batches',
        'warehouse_location' => 'warehouse-locations',
        'receiving' => 'receivings',
        'ticket' => 'tickets',
        'ticket_message' => 'tickets',
        'ticket_attachment' => 'tickets',
        'user' => 'users',
        'role' => 'roles',
    ];

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function log(
        string $actionType,
        string $entityType,
        ?int $entityId,
        string $module,
        string $description,
        ?int $userId = null,
        ?array $metadata = null,
    ): ActionLog {
        return ActionLog::create([
            'user_id' => $userId ?? auth('api')->id(),
            'action_type' => $actionType,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'module' => $module,
            'description' => $description,
            'metadata' => $metadata ? $this->sanitizeMetadata($metadata) : null,
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function logModelCreated(Model $model, ?string $description = null, ?array $metadata = null): ActionLog
    {
        $entityType = $this->resolveEntityType($model);
        $module = $this->resolveModule($entityType);

        return $this->log(
            actionType: 'create',
            entityType: $entityType,
            entityId: $model->getKey(),
            module: $module,
            description: $description ?? ucfirst(str_replace('_', ' ', $entityType))." #{$model->getKey()} created",
            metadata: $metadata,
        );
    }

    /**
     * @param  array<string, mixed>  $changes
     * @param  array<string, mixed>|null  $metadata
     */
    public function logModelUpdated(Model $model, array $changes = [], ?string $description = null, ?array $metadata = null): ActionLog
    {
        $entityType = $this->resolveEntityType($model);
        $module = $this->resolveModule($entityType);

        $logMetadata = $metadata ?? [];
        if ($changes !== []) {
            $logMetadata['changes'] = $this->sanitizeMetadata($changes);
        }

        return $this->log(
            actionType: 'update',
            entityType: $entityType,
            entityId: $model->getKey(),
            module: $module,
            description: $description ?? ucfirst(str_replace('_', ' ', $entityType))." #{$model->getKey()} updated",
            metadata: $logMetadata !== [] ? $logMetadata : null,
        );
    }

    public function logModelDeleted(Model $model, ?string $description = null): ActionLog
    {
        $entityType = $this->resolveEntityType($model);
        $module = $this->resolveModule($entityType);

        return $this->log(
            actionType: 'delete',
            entityType: $entityType,
            entityId: $model->getKey(),
            module: $module,
            description: $description ?? ucfirst(str_replace('_', ' ', $entityType))." #{$model->getKey()} deleted",
        );
    }

    public function logStatusChange(Model $model, string $oldStatus, string $newStatus, ?string $description = null): ActionLog
    {
        $entityType = $this->resolveEntityType($model);
        $module = $this->resolveModule($entityType);

        return $this->log(
            actionType: 'status_change',
            entityType: $entityType,
            entityId: $model->getKey(),
            module: $module,
            description: $description ?? ucfirst(str_replace('_', ' ', $entityType))." #{$model->getKey()} status changed from {$oldStatus} to {$newStatus}",
            metadata: [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ],
        );
    }

    public function logLogin(int $userId): ActionLog
    {
        return $this->log(
            actionType: 'login',
            entityType: 'user',
            entityId: $userId,
            module: 'users',
            description: "User #{$userId} logged in",
            userId: $userId,
        );
    }

    public function logLogout(int $userId): ActionLog
    {
        return $this->log(
            actionType: 'logout',
            entityType: 'user',
            entityId: $userId,
            module: 'users',
            description: "User #{$userId} logged out",
            userId: $userId,
        );
    }

    private function resolveEntityType(Model $model): string
    {
        $className = class_basename($model);

        return mb_strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
    }

    private function resolveModule(string $entityType): string
    {
        return self::ENTITY_MODULE_MAP[$entityType] ?? $entityType;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function sanitizeMetadata(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (in_array($key, self::SENSITIVE_FIELDS, true)) {
                continue;
            }

            $sanitized[$key] = is_array($value) ? $this->sanitizeMetadata($value) : $value;
        }

        return $sanitized;
    }
}
