<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ReceivingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $receipt_number
 * @property Carbon $receipt_datetime
 * @property string|null $supplier_invoice_number
 * @property int $batch_id
 * @property int $warehouse_location_id
 * @property float $received_quantity
 * @property string $unit
 * @property float|null $recorded_temperature
 * @property bool $temperature_compliant
 * @property string|null $temperature_notes
 * @property string $packaging_condition
 * @property string|null $quality_notes
 * @property bool $documents_verified
 * @property array<int, string>|null $missing_documents
 * @property string $status
 * @property string|null $rejection_reason
 * @property array<int, string>|null $photos
 * @property int $received_by_user_id
 * @property int|null $verified_by_user_id
 * @property string|null $notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Batch $batch
 * @property-read WarehouseLocation $warehouseLocation
 * @property-read User $receivedBy
 * @property-read User|null $verifiedBy
 */
#[Fillable([
    'receipt_number',
    'receipt_datetime',
    'supplier_invoice_number',
    'batch_id',
    'warehouse_location_id',
    'received_quantity',
    'unit',
    'recorded_temperature',
    'temperature_compliant',
    'temperature_notes',
    'packaging_condition',
    'quality_notes',
    'documents_verified',
    'missing_documents',
    'status',
    'rejection_reason',
    'photos',
    'received_by_user_id',
    'verified_by_user_id',
    'notes',
])]
final class Receiving extends Model
{
    /** @use HasFactory<ReceivingFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Batch, self>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * @return BelongsTo<WarehouseLocation, self>
     */
    public function warehouseLocation(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class);
    }

    /**
     * @return BelongsTo<User, self>
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    /**
     * @return BelongsTo<User, self>
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     *
     * @phpstan-return Builder<self>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('receipt_datetime', 'desc')
            ->orderBy('id', 'desc');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     *
     * @phpstan-return Builder<self>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     *
     * @phpstan-return Builder<self>
     */
    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('status', 'accepted');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     *
     * @phpstan-return Builder<self>
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     *
     * @phpstan-return Builder<self>
     */
    public function scopeQuarantined(Builder $query): Builder
    {
        return $query->where('status', 'quarantined');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     *
     * @phpstan-return Builder<self>
     */
    public function scopeTemperatureNonCompliant(Builder $query): Builder
    {
        return $query->where('temperature_compliant', false);
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isQuarantined(): bool
    {
        return $this->status === 'quarantined';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function hasPhotos(): bool
    {
        return $this->photos !== null && count($this->photos) > 0;
    }

    public function hasMissingDocuments(): bool
    {
        return $this->missing_documents !== null && count($this->missing_documents) > 0;
    }

    public function isTemperatureCompliant(): bool
    {
        return $this->temperature_compliant;
    }

    public function isDocumentsVerified(): bool
    {
        return $this->documents_verified;
    }

    public function isPackagingAcceptable(): bool
    {
        return in_array($this->packaging_condition, ['excellent', 'good', 'acceptable']);
    }

    public function areDocumentsComplete(): bool
    {
        return $this->documents_verified && ! $this->hasMissingDocuments();
    }

    protected static function newFactory(): ReceivingFactory
    {
        return ReceivingFactory::new();
    }

    protected function casts(): array
    {
        return [
            'receipt_datetime' => 'datetime',
            'received_quantity' => 'float',
            'recorded_temperature' => 'float',
            'temperature_compliant' => 'boolean',
            'documents_verified' => 'boolean',
            'missing_documents' => 'array',
            'photos' => 'array',
        ];
    }
}
