<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Models\Tenant;
use App\Models\Category;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class UploadController extends Controller
{
    protected FileUploadService $uploadService;

    public function __construct(FileUploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    /**
     * Upload tenant logo
     */
    public function uploadLogo(Request $request, Tenant $tenant): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'logo' => array_merge(['required'], FileUploadService::imageValidationRules(1024)),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation échouée',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Delete old logo if exists
            if ($tenant->logo_url) {
                $this->uploadService->delete($tenant->logo_url);
            }

            // Upload new logo
            $url = $this->uploadService->uploadLogo($request->file('logo'), $tenant->id);

            // Update tenant
            $tenant->update(['logo_url' => $url]);

            return response()->json([
                'success' => true,
                'message' => 'Logo uploadé avec succès',
                'url' => $url
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload tenant cover
     */
    public function uploadCover(Request $request, Tenant $tenant): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cover' => array_merge(['required'], FileUploadService::imageValidationRules(4096)),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation échouée',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Delete old cover if exists
            if ($tenant->cover_url) {
                $this->uploadService->delete($tenant->cover_url);
            }

            // Upload new cover
            $url = $this->uploadService->uploadCover($request->file('cover'), $tenant->id);

            // Update tenant
            $tenant->update(['cover_url' => $url]);

            return response()->json([
                'success' => true,
                'message' => 'Image de couverture uploadée avec succès',
                'url' => $url
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload dish photo
     */
    public function uploadDishPhoto(Request $request, Dish $dish): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'photo' => array_merge(['required'], FileUploadService::imageValidationRules(2048)),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation échouée',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Delete old photo if exists
            if ($dish->photo_url) {
                $this->uploadService->delete($dish->photo_url);
            }

            // Upload new photo
            $url = $this->uploadService->uploadDishPhoto($request->file('photo'), $dish->id);

            // Update dish
            $dish->update(['photo_url' => $url]);

            return response()->json([
                'success' => true,
                'message' => 'Photo du plat uploadée avec succès',
                'url' => $url
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload category image
     */
    public function uploadCategoryImage(Request $request, Category $category): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => array_merge(['required'], FileUploadService::imageValidationRules(1024)),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation échouée',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Delete old image if exists
            if ($category->image_url) {
                $this->uploadService->delete($category->image_url);
            }

            // Upload new image
            $url = $this->uploadService->uploadCategoryImage($request->file('image'), $category->id);

            // Update category
            $category->update(['image_url' => $url]);

            return response()->json([
                'success' => true,
                'message' => 'Image de la catégorie uploadée avec succès',
                'url' => $url
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete image with authorization check
     */
    public function deleteImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
            'type' => 'required|in:logo,cover,dish,category',
            'id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation échouée',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié'
            ], 401);
        }

        // Vérifier l'autorisation selon le type
        $authorized = false;
        switch ($request->type) {
            case 'logo':
            case 'cover':
                $tenant = Tenant::find($request->id);
                $authorized = $tenant && ($user->hasRole('SUPER_ADMIN') || $user->tenant_id === $tenant->id);
                break;
            case 'dish':
                $dish = Dish::find($request->id);
                $authorized = $dish && ($user->hasRole('SUPER_ADMIN') || $user->tenant_id === $dish->tenant_id);
                break;
            case 'category':
                $category = Category::with('menu')->find($request->id);
                $authorized = $category && $category->menu &&
                    ($user->hasRole('SUPER_ADMIN') || $user->tenant_id === $category->menu->tenant_id);
                break;
        }

        if (!$authorized) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé à supprimer cette image'
            ], 403);
        }

        try {
            $this->uploadService->delete($request->url);

            // Update the related model
            switch ($request->type) {
                case 'logo':
                    Tenant::find($request->id)?->update(['logo_url' => null]);
                    break;
                case 'cover':
                    Tenant::find($request->id)?->update(['cover_url' => null]);
                    break;
                case 'dish':
                    Dish::find($request->id)?->update(['photo_url' => null]);
                    break;
                case 'category':
                    Category::find($request->id)?->update(['image_url' => null]);
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Image supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }
}
