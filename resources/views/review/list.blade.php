@extends('layouts.app')

@section('title', 'Avis clients - ' . $tenant->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">

    {{-- Hero Header --}}
    <div class="pt-10 pb-6 px-4 text-center">
        @if($tenant->logo_url)
            <div class="w-20 h-20 mx-auto mb-4 rounded-2xl overflow-hidden bg-white p-1.5 shadow-xl">
                <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" class="w-full h-full object-contain rounded-xl">
            </div>
        @endif
        <h1 class="text-3xl font-extrabold tracking-tight" style="color: #f0abfc; text-shadow: 0 0 20px rgba(240,171,252,0.3);">{{ $tenant->name }}</h1>
        <p class="mt-2 text-slate-400 text-base">Ce que nos clients disent de nous</p>
    </div>

    <div class="max-w-2xl mx-auto px-4 pb-12">

        {{-- Rating Summary --}}
        @if($summary['total'] > 0)
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden mb-6">
            <div class="p-6">
                <div class="flex flex-col sm:flex-row items-center gap-6">
                    {{-- Overall score --}}
                    <div class="text-center flex-shrink-0">
                        <div class="text-5xl font-black text-slate-900">{{ number_format($summary['overall_average'], 1) }}</div>
                        <div class="flex items-center justify-center mt-2 gap-0.5">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-5 h-5 {{ $i <= round($summary['overall_average']) ? 'text-amber-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <p class="text-sm text-slate-500 mt-1 font-medium">{{ $summary['total'] }} avis</p>
                    </div>

                    {{-- Category scores --}}
                    <div class="flex-1 w-full space-y-3">
                        @foreach([
                            ['label' => 'Cuisine', 'value' => $summary['food_average'], 'emoji' => '🍽'],
                            ['label' => 'Service', 'value' => $summary['service_average'], 'emoji' => '🤝'],
                            ['label' => 'Ambiance', 'value' => $summary['ambiance_average'], 'emoji' => '✨']
                        ] as $cat)
                        <div class="flex items-center gap-3">
                            <span class="text-lg">{{ $cat['emoji'] }}</span>
                            <span class="text-sm font-semibold text-slate-700 w-20">{{ $cat['label'] }}</span>
                            <div class="flex-1 bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                <div class="h-full bg-amber-400 rounded-full transition-all" style="width: {{ ($cat['value'] / 5) * 100 }}%"></div>
                            </div>
                            <span class="text-sm font-bold text-slate-900 w-8 text-right">{{ number_format($cat['value'], 1) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- CTA --}}
        <div class="mb-6 text-center">
            <a href="{{ route('review.form', $tenant->slug) }}"
               class="inline-flex items-center gap-2 px-8 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-base rounded-2xl shadow-lg shadow-indigo-500/30 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Laisser un avis
            </a>
        </div>

        {{-- Reviews List --}}
        @if($reviews->isEmpty())
            <div class="bg-white rounded-3xl shadow-2xl p-12 text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Aucun avis pour le moment</h3>
                <p class="mt-2 text-slate-500">Soyez le premier a partager votre experience !</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($reviews as $review)
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden {{ $review->is_featured ? 'ring-2 ring-amber-400' : '' }}">
                        <div class="p-5">
                            {{-- Header --}}
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                        <span class="text-indigo-600 font-bold text-sm">{{ strtoupper(substr($review->display_name, 0, 2)) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 text-sm">{{ $review->display_name }}</p>
                                        <p class="text-xs text-slate-400">{{ $review->created_at->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                                @if($review->is_featured)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-700 rounded-full text-xs font-bold">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        Coup de coeur
                                    </span>
                                @endif
                            </div>

                            {{-- Stars --}}
                            <div class="flex items-center gap-3 mb-3">
                                <div class="flex gap-0.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $review->overall_rating ? 'text-amber-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                                <span class="text-xs text-slate-400 font-medium">
                                    🍽 {{ $review->food_rating }}/5 &middot; 🤝 {{ $review->service_rating }}/5 &middot; ✨ {{ $review->ambiance_rating }}/5
                                </span>
                            </div>

                            {{-- Comment --}}
                            @if($review->comment)
                                <p class="text-slate-700 text-sm leading-relaxed">{{ $review->comment }}</p>
                            @endif

                            {{-- Restaurant Response --}}
                            @if($review->response)
                                <div class="mt-4 bg-slate-50 rounded-xl p-4">
                                    <div class="flex items-start gap-3">
                                        <svg class="h-4 w-4 text-indigo-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                        </svg>
                                        <div>
                                            <p class="text-xs font-bold text-indigo-600 mb-1">Reponse de {{ $tenant->name }}</p>
                                            <p class="text-sm text-slate-600">{{ $review->response }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
