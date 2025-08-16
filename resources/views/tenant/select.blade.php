@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">Selecciona tu Empresa</h1>
        
        @if($tenants->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($tenants as $tenant)
                    <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-xl font-semibold text-gray-800">{{ $tenant->name }}</h3>
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($tenant->plan == 'trial') bg-yellow-100 text-yellow-800
                                    @elseif($tenant->plan == 'basic') bg-blue-100 text-blue-800
                                    @elseif($tenant->plan == 'premium') bg-purple-100 text-purple-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($tenant->plan) }}
                                </span>
                            </div>
                            
                            <p class="text-gray-600 mb-4">
                                <span class="block text-sm">
                                    <strong>Dominio:</strong> {{ $tenant->slug }}.avocontrol.pro
                                </span>
                                @if($tenant->pivot->role_within_tenant)
                                    <span class="block text-sm mt-1">
                                        <strong>Tu rol:</strong> {{ ucfirst($tenant->pivot->role_within_tenant) }}
                                    </span>
                                @endif
                            </p>
                            
                            <a href="http://{{ $tenant->slug }}.avocontrol.pro/dashboard" 
                               class="block w-full text-center bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded transition duration-200">
                                Acceder
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M12 7h.01M12 11h.01M12 15h.01"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No tienes empresas asignadas</h3>
                <p class="text-gray-500">Contacta a tu administrador para que te asigne a una empresa.</p>
            </div>
        @endif
    </div>
</div>
@endsection
