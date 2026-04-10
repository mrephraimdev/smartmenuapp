<?php

namespace App\Services;

use App\Models\Review;
use App\Models\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    /**
     * Create a new review
     */
    public function createReview(array $data): Review
    {
        return Review::create([
            'tenant_id' => $data['tenant_id'],
            'order_id' => $data['order_id'] ?? null,
            'table_id' => $data['table_id'] ?? null,
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'] ?? null,
            'food_rating' => $data['food_rating'],
            'service_rating' => $data['service_rating'],
            'ambiance_rating' => $data['ambiance_rating'],
            'overall_rating' => $data['overall_rating'] ?? null,
            'comment' => $data['comment'] ?? null,
            'is_anonymous' => $data['is_anonymous'] ?? false,
            'is_published' => false, // Auto-moderation
        ]);
    }

    /**
     * Get published reviews for a tenant
     */
    public function getPublishedReviews(int $tenantId, int $limit = 20): Collection
    {
        return Review::where('tenant_id', $tenantId)
            ->where('is_published', true)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get featured reviews for a tenant
     */
    public function getFeaturedReviews(int $tenantId, int $limit = 5): Collection
    {
        return Review::where('tenant_id', $tenantId)
            ->where('is_published', true)
            ->where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get pending reviews for moderation
     */
    public function getPendingReviews(int $tenantId): Collection
    {
        return Review::where('tenant_id', $tenantId)
            ->where('is_published', false)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Moderate review (publish/reject)
     */
    public function moderateReview(Review $review, bool $approve): Review
    {
        if ($approve) {
            $review->publish();
        } else {
            $review->delete(); // Or mark as rejected
        }

        return $review;
    }

    /**
     * Add response to review
     */
    public function addResponse(Review $review, string $response): Review
    {
        return $review->addResponse($response);
    }

    /**
     * Get rating summary
     */
    public function getRatingSummary(int $tenantId): array
    {
        return Review::getRatingSummary($tenantId);
    }

    /**
     * Get reviews by rating
     */
    public function getReviewsByRating(int $tenantId, int $rating): Collection
    {
        return Review::where('tenant_id', $tenantId)
            ->where('is_published', true)
            ->where('overall_rating', $rating)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get recent reviews with low ratings for attention
     */
    public function getLowRatingReviews(int $tenantId, int $maxRating = 3, int $days = 30): Collection
    {
        return Review::where('tenant_id', $tenantId)
            ->where('is_published', true)
            ->where('overall_rating', '<=', $maxRating)
            ->where('created_at', '>=', now()->subDays($days))
            ->whereNull('response')
            ->orderBy('overall_rating')
            ->get();
    }

    /**
     * Calculate average ratings over time
     */
    public function getAverageRatingsOverTime(int $tenantId, int $days = 30): array
    {
        $data = Review::where('tenant_id', $tenantId)
            ->where('is_published', true)
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('AVG(overall_rating) as avg_rating')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $data->map(fn($row) => [
            'date' => $row->date,
            'average' => round($row->avg_rating, 1),
            'count' => $row->count
        ])->toArray();
    }

    /**
     * Get review statistics
     */
    public function getStatistics(int $tenantId): array
    {
        $summary = $this->getRatingSummary($tenantId);

        $recentTrend = Review::where('tenant_id', $tenantId)
            ->where('is_published', true)
            ->where('created_at', '>=', now()->subDays(30))
            ->avg('overall_rating');

        $previousTrend = Review::where('tenant_id', $tenantId)
            ->where('is_published', true)
            ->where('created_at', '>=', now()->subDays(60))
            ->where('created_at', '<', now()->subDays(30))
            ->avg('overall_rating');

        $trendDirection = 'stable';
        if ($recentTrend && $previousTrend) {
            if ($recentTrend > $previousTrend + 0.2) {
                $trendDirection = 'up';
            } elseif ($recentTrend < $previousTrend - 0.2) {
                $trendDirection = 'down';
            }
        }

        return array_merge($summary, [
            'recent_average' => round($recentTrend ?? 0, 1),
            'trend_direction' => $trendDirection,
            'pending_count' => Review::where('tenant_id', $tenantId)
                ->where('is_published', false)
                ->count(),
            'unanswered_count' => Review::where('tenant_id', $tenantId)
                ->where('is_published', true)
                ->whereNull('response')
                ->count(),
        ]);
    }
}
