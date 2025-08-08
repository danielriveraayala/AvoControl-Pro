<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Registrar Nueva Venta
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('sales.store') }}" id="saleForm">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <!-- Cliente -->
                            <div>
                                <label for="customer_id" class="block text-sm font-medium text-gray-700">
                                    Cliente *
                                </label>
                                <select name="customer_id" id="customer_id" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Seleccione un cliente</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->company_name }} - {{ $customer->contact_person }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Fecha de Venta -->
                            <div>
                                <label for="sale_date" class="block text-sm font-medium text-gray-700">
                                    Fecha de Venta *
                                </label>
                                <input type="date" name="sale_date" id="sale_date" required
                                    value="{{ old('sale_date', date('Y-m-d')) }}"
                                    max="{{ date('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('sale_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Fecha de Entrega -->
                            <div>
                                <label for="delivery_date" class="block text-sm font-medium text-gray-700">
                                    Fecha de Entrega (Opcional)
                                </label>
                                <input type="date" name="delivery_date" id="delivery_date"
                                    value="{{ old('delivery_date') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Número de Factura -->
                            <div>
                                <label for="invoice_number" class="block text-sm font-medium text-gray-700">
                                    Número de Factura (Opcional)
                                </label>
                                <input type="text" name="invoice_number" id="invoice_number"
                                    value="{{ old('invoice_number') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>

                        <!-- Items de Venta -->
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Items de Venta</h3>
                                <button type="button" onclick="addItem()" 
                                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                                    + Agregar Item
                                </button>
                            </div>

                            <div id="items-container">
                                <!-- Items dinámicos se agregarán aquí -->
                            </div>
                        </div>

                        <!-- Resumen -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <div class="grid grid-cols-3 gap-4">
                                <div class="text-center">
                                    <p class="text-sm text-gray-600">Peso Total</p>
                                    <p class="text-2xl font-bold text-gray-900"><span id="totalWeight">0.00</span> kg</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm text-gray-600">Items</p>
                                    <p class="text-2xl font-bold text-gray-900"><span id="totalItems">0</span></p>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm text-gray-600">Monto Total</p>
                                    <p class="text-2xl font-bold text-green-600">$<span id="totalAmount">0.00</span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex items-center justify-between">
                            <a href="{{ route('sales.index') }}" 
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancelar
                            </a>
                            <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Guardar Venta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const lots = @json($lots);
        let itemCount = 0;

        function addItem() {
            const container = document.getElementById('items-container');
            const itemDiv = document.createElement('div');
            itemDiv.className = 'border border-gray-200 rounded-lg p-4 mb-4';
            itemDiv.id = `item-${itemCount}`;
            
            itemDiv.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Lote *</label>
                        <select name="items[${itemCount}][lot_id]" required onchange="updateLotInfo(${itemCount})"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Seleccione un lote</option>
                            ${lots.map(lot => `
                                <option value="${lot.id}" data-available="${lot.weight_available}" 
                                    data-price="${lot.purchase_price_per_kg}" data-quality="${lot.quality_grade}">
                                    ${lot.lot_code} - ${lot.quality_grade} - ${lot.weight_available} kg disponibles
                                </option>
                            `).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Peso (kg) *</label>
                        <input type="number" name="items[${itemCount}][weight]" required
                            step="0.01" min="0.01" max="0"
                            onchange="updateTotals()"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <span class="text-xs text-gray-500">Máx: <span id="max-weight-${itemCount}">0</span> kg</span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Precio/kg *</label>
                        <input type="number" name="items[${itemCount}][price_per_kg]" required
                            step="0.01" min="0.01"
                            onchange="updateTotals()"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div class="flex items-end">
                        <button type="button" onclick="removeItem(${itemCount})"
                            class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm w-full">
                            Eliminar
                        </button>
                    </div>
                </div>
            `;
            
            container.appendChild(itemDiv);
            itemCount++;
            updateTotals();
        }

        function removeItem(id) {
            const item = document.getElementById(`item-${id}`);
            if (item) {
                item.remove();
                updateTotals();
            }
        }

        function updateLotInfo(itemId) {
            const select = document.querySelector(`select[name="items[${itemId}][lot_id]"]`);
            const weightInput = document.querySelector(`input[name="items[${itemId}][weight]"]`);
            const priceInput = document.querySelector(`input[name="items[${itemId}][price_per_kg]"]`);
            const maxWeightSpan = document.getElementById(`max-weight-${itemId}`);
            
            if (select && select.value) {
                const option = select.options[select.selectedIndex];
                const available = parseFloat(option.dataset.available);
                const price = parseFloat(option.dataset.price);
                
                weightInput.max = available;
                maxWeightSpan.textContent = available.toFixed(2);
                priceInput.value = (price * 1.3).toFixed(2); // 30% markup default
            }
            
            updateTotals();
        }

        function updateTotals() {
            let totalWeight = 0;
            let totalAmount = 0;
            let itemsCount = 0;
            
            const container = document.getElementById('items-container');
            const items = container.querySelectorAll('[id^="item-"]');
            
            items.forEach(item => {
                const weightInput = item.querySelector('input[name*="[weight]"]');
                const priceInput = item.querySelector('input[name*="[price_per_kg]"]');
                
                if (weightInput && priceInput) {
                    const weight = parseFloat(weightInput.value) || 0;
                    const price = parseFloat(priceInput.value) || 0;
                    
                    totalWeight += weight;
                    totalAmount += weight * price;
                    if (weight > 0) itemsCount++;
                }
            });
            
            document.getElementById('totalWeight').textContent = totalWeight.toFixed(2);
            document.getElementById('totalItems').textContent = itemsCount;
            document.getElementById('totalAmount').textContent = totalAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        // Add first item on load
        document.addEventListener('DOMContentLoaded', function() {
            addItem();
        });

        // Validate form before submit
        document.getElementById('saleForm').addEventListener('submit', function(e) {
            const items = document.querySelectorAll('[id^="item-"]');
            if (items.length === 0) {
                e.preventDefault();
                alert('Debe agregar al menos un item a la venta');
                return false;
            }
        });
    </script>
    @endpush
</x-app-layout>