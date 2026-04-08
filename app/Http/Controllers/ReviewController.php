<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Tenant;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    public function __construct(
        private ReviewService $reviewService
    ) {}

    /**
     * Display reviews list (admin)
     */
    public function index(string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $reviews = Review::where('tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        $statistics = $this->reviewService->getStatistics($tenant->id);

        return view('admin.reviews.index', compact('tenant', 'reviews', 'statistics'));
    }

    /**
     * Store new review (public API)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'order_id' => 'nullable|exists:orders,id',
            'table_id' => 'nullable|exists:tables,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'food_rating' => 'required|integer|min:1|max:5',
            'service_rating' => 'required|integer|min:1|max:5',
            'ambiance_rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
            'is_anonymous' => 'boolean'
        ]);

        try {
            $review = $this->reviewService->createReview($validated);

            return response()->json([
                'success' => true,
                'message' => 'Merci pour votre avis ! Il sera publié après modération.',
                'review_id' => $review->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de votre avis'
            ], 500);
        }
    }

    /**
     * Get published reviews (public API)
     */
    public function getPublished(int $tenantId): JsonResponse
    {
        $reviews = $this->reviewService->getPublishedReviews($tenantId);
        $summary = $this->reviewService->getRatingSummary($tenantId);

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'reviews' => $reviews->map(fn($r) => [
                'id' => $r->id,
                'name' => $r->display_name,
                'overall_rating' => $r->overall_rating,
                'food_rating' => $r->food_rating,
                'service_rating' => $r->service_rating,
                'ambiance_rating' => $r->ambiance_rating,
                'comment' => $r->comment,
                'response' => $r->response,
                'date' => $r->created_at->format('d/m/Y'),
                'is_featured' => $r->is_featured
            ])
        ]);
    }

    /**
     * Get featured reviews (public API)
     */
    public function getFeatured(int $tenantId): JsonResponse
    {
        $reviews = $this->reviewService->getFeaturedReviews($tenantId);

        return response()->json([
            'success' => true,
            'reviews' => $reviews->map(fn($r) => [
                'name' => $r->display_name,
                'overall_rating' => $r->overall_rating,
                'comment' => $r->comment,
                'date' => $r->created_at->format('d/m/Y')
            ])
        ]);
    }

    /**
     * Moderate review (admin)
     */
    public function moderate(Request $request, string $tenantSlug, Review $review): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject'
        ]);

        $this->reviewService->moderateReview($review, $validated['action'] === 'approve');

        return response()->json([
            'success' => true,
            'message' => $validated['action'] === 'approve'
                ? 'Avis publié'
                : 'Avis rejeté'
        ]);
    }

    /**
     * Add response to review (admin)
     */
    public function respond(Request $request, string $tenantSlug, Review $review): JsonResponse
    {
        $validated = $request->validate([
            'response' => 'required|string|max:2000'
        ]);

        $this->reviewService->addResponse($review, $validated['response']);

        return response()->json([
            'success' => true,
            'message' => 'Réponse ajoutée'
        ]);
    }

    /**
     * Toggle feature status (admin)
     */
    public function toggleFeature(string $tenantSlug, Review $review): JsonResponse
    {
        if ($review->is_featured) {
            $review->unfeature();
            $message = 'Avis retiré des mis en avant';
        } else {
            $review->feature();
            $message = 'Avis mis en avant';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_featured' => $review->is_featured
        ]);
    }

    /**
     * Delete review (admin)
     */
    public function destroy(string $tenantSlug, Review $review): JsonResponse
    {
        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Avis supprimé'
        ]);
    }

    /**
     * Get reviews statistics
     */
    public function statistics(string $tenantSlug): JsonResponse
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $statistics = $this->reviewService->getStatistics($tenant->id);
        $trend = $this->reviewService->getAverageRatingsOverTime($tenant->id);

        return response()->json([
            'success' => true,
            'statistics' => $statistics,
            'trend' => $trend
        ]);
    }

    /**
     * Show review details (admin)
     */
    public function show(string $tenantSlug, Review $review)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $review->load(['order', 'table']);

        return view('admin.reviews.show', compact('tenant', 'review'));
    }

    /**
     * Approve review (admin)
     */
    public function approve(string $tenantSlug, Review $review): JsonResponse
    {
        $this->reviewService->moderateReview($review, true);

        return response()->json([
            'success' => true,
            'message' => 'Avis approuvé et publié'
        ]);
    }

    /**
     * Reject review (admin)
     */
    public function reject(string $tenantSlug, Review $review): JsonResponse
    {
        $this->reviewService->moderateReview($review, false);

        return response()->json([
            'success' => true,
            'message' => 'Avis rejeté'
        ]);
    }

    /**
     * Public review form
     */
    public function publicForm(string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        return view('review.form', compact('tenant'));
    }

    /**
     * Public store review
     * Accepte soit un rating global simple, soit les 3 ratings détaillés
     */
    public function publicStore(Request $request, string $tenantSlug): JsonResponse
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        // Validation flexible : accepte rating global OU ratings détaillés
        $validated = $request->validate([
            'order_id' => 'nullable|exists:orders,id',
            'table_id' => 'nullable|exists:tables,id',
            'table_code' => 'nullable|string|max:50',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            // Rating global simple (utilisé si les ratings détaillés ne sont pas fournis)
            'rating' => 'required_without_all:food_rating,service_rating,ambiance_rating|integer|min:1|max:5',
            // Ratings détaillés (optionnels si rating global fourni)
            'food_rating' => 'nullable|integer|min:1|max:5',
            'service_rating' => 'nullable|integer|min:1|max:5',
            'ambiance_rating' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
            'is_anonymous' => 'boolean'
        ]);

        $validated['tenant_id'] = $tenant->id;

        // Si rating global fourni, l'utiliser pour les 3 ratings
        if (isset($validated['rating']) && !isset($validated['food_rating'])) {
            $validated['food_rating'] = $validated['rating'];
            $validated['service_rating'] = $validated['rating'];
            $validated['ambiance_rating'] = $validated['rating'];
        }

        // Nom par défaut si non fourni
        if (empty($validated['customer_name'])) {
            $validated['customer_name'] = 'Client';
            $validated['is_anonymous'] = true;
        }

        // Trouver la table par code si fourni
        if (!empty($validated['table_code']) && empty($validated['table_id'])) {
            $table = $tenant->tables()->where('code', $validated['table_code'])->first();
            if ($table) {
                $validated['table_id'] = $table->id;
            }
        }

        try {
            $review = $this->reviewService->createReview($validated);

            return response()->json([
                'success' => true,
                'message' => 'Merci pour votre avis ! Il sera publié après modération.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de votre avis'
            ], 500);
        }
    }

    /**
     * Public reviews list
     */
    public function publicList(string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $reviews = $this->reviewService->getPublishedReviews($tenant->id);
        $summary = $this->reviewService->getRatingSummary($tenant->id);

        return view('review.list', compact('tenant', 'reviews', 'summary'));
    }
}
