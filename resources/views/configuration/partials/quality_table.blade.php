<table class="table table-bordered table-hover table-striped w-100" id="qualityTable" style="width: 100%">
    <thead class="bg-light w-100">
        <tr>
            <th>Orden</th>
            <th>Nombre</th>
            <th>Color</th>
            <th>Peso (g)</th>
            <th>Descripción</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody class="w-100">
        @forelse($qualityGrades as $quality)
            <tr>
                <td>
                    <span class="badge badge-secondary">{{ $quality->sort_order }}</span>
                </td>
                <td>
                    <strong>{{ $quality->name }}</strong>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="color-preview me-2" style="width: 20px; height: 20px; background-color: {{ $quality->color ?? '#6c757d' }}; border-radius: 4px; border: 1px solid #ccc;"></div>
                        <small class="text-muted">{{ $quality->color ?? '#6c757d' }}</small>
                    </div>
                </td>
                <td>
                    <span class="text-primary">
                        @if($quality->weight_min && $quality->weight_max)
                            {{ $quality->weight_min }}g - {{ $quality->weight_max }}g
                        @elseif($quality->weight_min)
                            {{ $quality->weight_min }}g+
                        @elseif($quality->weight_max)
                            {{ $quality->weight_max }}g-
                        @else
                            Sin especificar
                        @endif
                    </span>
                </td>
                <td>
                    <small class="text-muted">{{ $quality->description ? (strlen($quality->description) > 50 ? substr($quality->description, 0, 50) . '...' : $quality->description) : '' }}</small>
                </td>
                <td>
                    <span class="badge badge-{{ $quality->active ? 'success' : 'secondary' }}">
                        {{ $quality->active ? 'Activo' : 'Inactivo' }}
                    </span>
                </td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-info" onclick="editQuality({{ $quality->id }})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger" onclick="deleteQuality({{ $quality->id }}, '{{ $quality->name }}')" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center text-muted">
                    <i class="fas fa-star fa-2x mb-2 d-block"></i>
                    No hay calidades configuradas. <a href="#" onclick="openCreateQualityModal()">Crear la primera calidad</a>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
