<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'label' => $this->label,
            'capacity' => $this->capacity,
            'is_active' => (bool) $this->is_active,
            'qr_code_url' => $this->qr_code_url,
            'menu_url' => $this->when($this->tenant_id, function () {
                return url("/menu/{$this->tenant_id}/{$this->code}");
            }),
            'qr_image_url' => $this->when($this->tenant_id, function () {
                $menuUrl = url("/menu/{$this->tenant_id}/{$this->code}");
                return "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($menuUrl);
            }),
            'tenant' => new TenantResource($this->whenLoaded('tenant')),
            'tenant_id' => $this->tenant_id,
            'orders_count' => $this->whenCounted('orders'),
            'active_orders_count' => $this->when(
                $this->relationLoaded('orders'),
                fn() => $this->orders->where('status', '!=', 'SERVI')->where('status', '!=', 'ANNULE')->count()
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'links' => [
                'self' => $this->when($this->id, fn() => route('admin.tables.show', [$this->tenant?->slug ?? 'default', $this->id], false)),
                'qr_code' => $this->when($this->tenant_id, fn() => route('qrcode.show', [$this->tenant_id, $this->code], false)),
            ],
        ];
    }
}
