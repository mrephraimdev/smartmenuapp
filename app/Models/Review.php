<?php

namespace App\Models;

use App\Traits\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory, TenantScope;

    protected $fillable = [
        'tenant_id',
        'order_id',
        'table_id',
        'customer_name',
        'customer_email',
        'food_rating',
        'service_rating',
        'ambiance_rating',
        'overall_rating',
        'comment',
        'is_anonymous',
        'is_published',
        'is_featured',
        'response',
        'responded_at',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'responded_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($review) {
            // Calculate overall rating if not set
            if (!$review->overall_rating) {
                $review->overall_rating = round(
                    ($review->food_rating + $review->service_rating + $review->ambiance_rating) / 3
                );
            }
        });
    }

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Get display name
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->is_anonymous) {
            return 'Client anonyme';
        }

        return $this->customer_name;
    }

    /**
     * Get status attribute (PENDING, PUBLISHED, REJECTED)
     */
    public function getStatusAttribute(): string
    {
        if ($this->is_published) {
            return 'PUBLISHED';
        }

        return 'PENDING';
    }

    /**
     * Check if has response
     */
    public function hasResponse(): bool
    {
        return !empty($this->response);
    }

    /**
     * Add response
     */
    public function addResponse(string $response): self
    {
        $this->update([
            'response' => $response,
            'responded_at' => now(),
        ]);

        return $this;
    }

    /**
     * Publish review
     */
    public function publish(): self
    {
        $this->update(['is_published' => true]);
        return $this;
    }

    /**
     * Unpublish review
     */
    public function unpublish(): self
    {
        $this->update(['is_published' => false]);
        return $this;
    }

    /**
     * Feature review
     */
    public function feature(): self
    {
        $this->update(['is_featured' => true]);
        return $this;
    }

    /**
     * Unfeature review
     */
    public function unfeature(): self
    {
        $this->update(['is_featured' => false]);
        return $this;
    }

    /**
     * Calculate average rating
     */
    public function getAverageRatingAttribute(): float
    {
        return round(
            ($this->food_rating + $this->service_rating + $this->ambiance_rating) / 3,
            1
        );
    }

    /**
     * Scopes
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeWithMinRating($query, int $minRating)
    {
        return $query->where('overall_rating', '>=', $minRating);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get rating summary for a tenant
     */
    public static function getRatingSummary(int $tenantId): array
    {
        $reviews = self::where('tenant_id', $tenantId)
            ->where('is_published', true)
            ->get();

        if ($reviews->isEmpty()) {
            return [
                'total' => 0,
                'average' => 0,
                'food_average' => 0,
                'service_average' => 0,
                'ambiance_average' => 0,
                'distribution' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
            ];
        }

        $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        foreach ($reviews as $review) {
            $distribution[$review->overall_rating]++;
        }

        return [
            'total' => $reviews->count(),
            'average' => round($reviews->avg('overall_rating'), 1),
            'food_average' => round($reviews->avg('food_rating'), 1),
            'service_average' => round($reviews->avg('service_rating'), 1),
            'ambiance_average' => round($reviews->avg('ambiance_rating'), 1),
            'distribution' => $distribution,
        ];
    }
}
