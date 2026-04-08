@extends('layouts.app')

@section('title', 'Confirmation de réservation - ' . $tenant->name)

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-lg mx-auto">
        <!-- Success Icon -->
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Réservation confirmée !</h1>
            <p class="mt-2 text-gray-600">Votre demande de réservation a été enregistrée</p>
        </div>

        <!-- Confirmation Card -->
        <div class="mt-8 bg-white shadow-lg rounded-lg overflow-hidden">
            <!-- Header with confirmation code -->
            <div class="bg-primary-600 px-6 py-4 text-center">
                <p class="text-primary-100 text-sm">Code de confirmation</p>
                <p class="text-2xl font-bold text-white tracking-wider">{{ $reservation->confirmation_code }}</p>
            </div>

            <!-- Reservation Details -->
            <div class="px-6 py-6 space-y-4">
                <div class="flex items-start">
                    <svg class="h-5 w-5 text-gray-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <div>
                        <p class="text-sm text-gray-500">Restaurant</p>
                        <p class="font-medium text-gray-900">{{ $tenant->name }}</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <svg class="h-5 w-5 text-gray-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <div>
                        <p class="text-sm text-gray-500">Date et heure</p>
                        <p class="font-medium text-gray-900">
                            {{ $reservation->reservation_date->format('l d F Y') }}
                            à {{ $reservation->reservation_time->format('H:i') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-start">
                    <svg class="h-5 w-5 text-gray-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <div>
                        <p class="text-sm text-gray-500">Nombre de personnes</p>
                        <p class="font-medium text-gray-900">{{ $reservation->party_size }} personne{{ $reservation->party_size > 1 ? 's' : '' }}</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <svg class="h-5 w-5 text-gray-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <div>
                        <p class="text-sm text-gray-500">Réservé au nom de</p>
                        <p class="font-medium text-gray-900">{{ $reservation->customer_name }}</p>
                    </div>
                </div>

                @if($reservation->special_requests)
                <div class="flex items-start">
                    <svg class="h-5 w-5 text-gray-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                    </svg>
                    <div>
                        <p class="text-sm text-gray-500">Demandes spéciales</p>
                        <p class="font-medium text-gray-900">{{ $reservation->special_requests }}</p>
                    </div>
                </div>
                @endif

                <!-- Status Badge -->
                <div class="pt-4 border-t">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Statut</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($reservation->status === 'PENDING') bg-yellow-100 text-yellow-800
                            @elseif($reservation->status === 'CONFIRMED') bg-green-100 text-green-800
                            @elseif($reservation->status === 'CANCELLED') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $reservation->status_label }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 space-y-3">
            <a href="{{ route('menu.client', ['tenantId' => $tenant->id, 'tableId' => $reservation->table_id]) }}"
               class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Voir le menu
            </a>

            <a href="{{ route('reservation.form', $tenant->slug) }}"
               class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Nouvelle réservation
            </a>
        </div>

        <!-- Contact -->
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>Pour modifier ou annuler votre réservation, contactez-nous :</p>
            <p class="mt-1">
                <a href="tel:{{ $tenant->phone }}" class="text-primary-600 hover:text-primary-500">{{ $tenant->phone }}</a>
            </p>
        </div>
    </div>
</div>
@endsection
