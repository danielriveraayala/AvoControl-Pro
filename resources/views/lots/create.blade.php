<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Registrar Nuevo Lote
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('lots.store') }}">
                        @csrf

                        <!-- Proveedor -->
                        <div class="mb-4">
                            <label for="supplier_id" class="block text-sm font-medium text-gray-700">
                                Proveedor *
                            </label>
                            <select name="supplier_id" id="supplier_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('supplier_id') border-red-500 @enderror">
                                <option value="">Seleccione un proveedor</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }} - {{ $supplier->phone ?? 'Sin teléfono' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fecha de Cosecha -->
                        <div class="mb-4">
                            <label for="harvest_date" class="block text-sm font-medium text-gray-700">
                                Fecha de Cosecha *
                            </label>
                            <input type="date" name="harvest_date" id="harvest_date" required
                                value="{{ old('harvest_date') }}"
                                max="{{ date('Y-m-d') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('harvest_date') border-red-500 @enderror">
                            @error('harvest_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fecha de Entrada -->
                        <div class="mb-4">
                            <label for="entry_date" class="block text-sm font-medium text-gray-700">
                                Fecha de Entrada *
                            </label>
                            <input type="datetime-local" name="entry_date" id="entry_date" required
                                value="{{ old('entry_date', date('Y-m-d\TH:i')) }}"
                                max="{{ date('Y-m-d\TH:i') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('entry_date') border-red-500 @enderror">
                            @error('entry_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Calidad -->
                        <div class="mb-4">
                            <label for="quality_grade" class="block text-sm font-medium text-gray-700">
                                Grado de Calidad *
                            </label>
                            <select name="quality_grade" id="quality_grade" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('quality_grade') border-red-500 @enderror">
                                <option value="">Seleccione la calidad</option>
                                <option value="Primera" {{ old('quality_grade') == 'Primera' ? 'selected' : '' }}>Primera</option>
                                <option value="Segunda" {{ old('quality_grade') == 'Segunda' ? 'selected' : '' }}>Segunda</option>
                                <option value="Tercera" {{ old('quality_grade') == 'Tercera' ? 'selected' : '' }}>Tercera</option>
                            </select>
                            @error('quality_grade')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Peso Total -->
                        <div class="mb-4">
                            <label for="total_weight" class="block text-sm font-medium text-gray-700">
                                Peso Total (kg) *
                            </label>
                            <input type="number" name="total_weight" id="total_weight" required
                                value="{{ old('total_weight') }}"
                                step="0.01" min="0.01"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('total_weight') border-red-500 @enderror"
                                onchange="calculateTotal()">
                            @error('total_weight')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Precio por Kg -->
                        <div class="mb-4">
                            <label for="purchase_price_per_kg" class="block text-sm font-medium text-gray-700">
                                Precio por Kg ($) *
                            </label>
                            <input type="number" name="purchase_price_per_kg" id="purchase_price_per_kg" required
                                value="{{ old('purchase_price_per_kg') }}"
                                step="0.01" min="0.01"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('purchase_price_per_kg') border-red-500 @enderror"
                                onchange="calculateTotal()">
                            @error('purchase_price_per_kg')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Costo Total (Calculado) -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">
                                Costo Total
                            </label>
                            <div class="mt-1 p-3 bg-gray-100 rounded-md">
                                <span class="text-2xl font-bold text-gray-900">$</span>
                                <span id="total_cost" class="text-2xl font-bold text-gray-900">0.00</span>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex items-center justify-between mt-6">
                            <a href="{{ route('lots.index') }}" 
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancelar
                            </a>
                            <button type="submit" 
                                class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Guardar Lote
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function calculateTotal() {
            const weight = parseFloat(document.getElementById('total_weight').value) || 0;
            const price = parseFloat(document.getElementById('purchase_price_per_kg').value) || 0;
            const total = weight * price;
            document.getElementById('total_cost').textContent = total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        // Calculate on page load if values exist
        document.addEventListener('DOMContentLoaded', calculateTotal);
    </script>
    @endpush
</x-app-layout>