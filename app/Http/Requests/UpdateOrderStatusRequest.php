<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // SUPER_ADMIN can update any order status
        if ($user->hasRole(UserRole::SUPER_ADMIN->value)) {
            return true;
        }

        // ADMIN, CHEF, SERVEUR can update orders from their tenant
        if ($user->hasRole(UserRole::ADMIN->value) ||
            $user->hasRole(UserRole::CHEF->value) ||
            $user->hasRole(UserRole::SERVEUR->value)) {
            $order = $this->route('order') ?? $this->route('id');
            if (is_numeric($order)) {
                $order = \App\Models\Order::find($order);
            }
            return $order && $order->tenant_id === $user->tenant_id;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in(array_column(OrderStatus::cases(), 'value')),
            ],
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        $validStatuses = implode(', ', array_map(
            fn($status) => $status->value . ' (' . $status->label() . ')',
            OrderStatus::cases()
        ));

        return [
            'status.required' => 'Le statut est requis.',
            'status.in' => "Le statut doit être l'un des suivants : {$validStatuses}.",
            'notes.max' => 'Les notes ne peuvent pas dépasser 500 caractères.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'status' => 'statut',
            'notes' => 'notes',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateStatusTransition($validator);
        });
    }

    /**
     * Validate that the status transition is valid.
     */
    protected function validateStatusTransition($validator): void
    {
        $order = $this->route('order') ?? $this->route('id');
        if (is_numeric($order)) {
            $order = \App\Models\Order::find($order);
        }

        if (!$order) {
            return;
        }

        $currentStatus = OrderStatus::tryFrom($order->status);
        $newStatus = OrderStatus::tryFrom($this->status);

        if (!$currentStatus || !$newStatus) {
            return;
        }

        // Cannot change status of already served or cancelled orders
        if (in_array($currentStatus, [OrderStatus::SERVED, OrderStatus::CANCELLED])) {
            $validator->errors()->add('status',
                'Impossible de modifier le statut d\'une commande ' . $currentStatus->label() . '.'
            );
            return;
        }

        // Define valid transitions
        $validTransitions = [
            OrderStatus::RECEIVED->value => [
                OrderStatus::PREPARING->value,
                OrderStatus::CANCELLED->value
            ],
            OrderStatus::PREPARING->value => [
                OrderStatus::READY->value,
                OrderStatus::CANCELLED->value
            ],
            OrderStatus::READY->value => [
                OrderStatus::SERVED->value,
                OrderStatus::CANCELLED->value
            ],
        ];

        $allowedNextStatuses = $validTransitions[$currentStatus->value] ?? [];

        if (!in_array($newStatus->value, $allowedNextStatuses)) {
            $allowedLabels = array_map(
                fn($s) => OrderStatus::from($s)->label(),
                $allowedNextStatuses
            );
            $validator->errors()->add('status',
                'Transition invalide. Depuis "' . $currentStatus->label() .
                '", vous pouvez passer à : ' . implode(', ', $allowedLabels) . '.'
            );
        }
    }
}
