@extends('layouts.admin')

@section('title', 'Perfil')
@section('page-title', 'Mi Perfil')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Mi Perfil</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-3">
            <!-- Profile Image -->
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle"
                             src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=007bff&color=fff&size=128"
                             alt="User profile picture">
                    </div>

                    <h3 class="profile-username text-center">{{ Auth::user()->name }}</h3>
                    <p class="text-muted text-center">{{ ucfirst(Auth::user()->role ?? 'Usuario') }}</p>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Email</b> <a class="float-right">{{ Auth::user()->email }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Miembro desde</b> <a class="float-right">{{ Auth::user()->created_at->format('d/m/Y') }}</a>
                        </li>
                        <li class="list-group-item">
                            <b>Última conexión</b> <a class="float-right">{{ Auth::user()->updated_at->diffForHumans() }}</a>
                        </li>
                    </ul>
                </div>
            </div>

        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header p-2">
                    <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#activity" data-toggle="tab">Información Personal</a></li>
                        <li class="nav-item"><a class="nav-link" href="#security" data-toggle="tab">Seguridad</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Personal Information Tab -->
                        <div class="active tab-pane" id="activity">
                            <form class="form-horizontal" id="profileForm">
                                @csrf
                                <div class="form-group row">
                                    <label for="name" class="col-sm-2 col-form-label">Nombre</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="name" name="name"
                                               value="{{ Auth::user()->name }}" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="email" class="col-sm-2 col-form-label">Email</label>
                                    <div class="col-sm-10">
                                        <input type="email" class="form-control" id="email" name="email"
                                               value="{{ Auth::user()->email }}" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="phone" class="col-sm-2 col-form-label">Teléfono</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="phone" name="phone"
                                               value="{{ Auth::user()->phone ?? '' }}" placeholder="(452) 123-4567">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="department" class="col-sm-2 col-form-label">Departamento</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="department" name="department"
                                               value="{{ Auth::user()->department ?? '' }}" placeholder="Administración">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="bio" class="col-sm-2 col-form-label">Biografía</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control" id="bio" name="bio" rows="4"
                                                  placeholder="Cuéntanos un poco sobre ti...">{{ Auth::user()->bio ?? '' }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="offset-sm-2 col-sm-10">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Guardar Cambios
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>


                        <!-- Security Tab -->
                        <div class="tab-pane" id="security">
                            <form class="form-horizontal" id="passwordForm">
                                @csrf
                                <div class="form-group row">
                                    <label for="current_password" class="col-sm-2 col-form-label">Contraseña Actual</label>
                                    <div class="col-sm-10">
                                        <input type="password" class="form-control" id="current_password"
                                               name="current_password" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="new_password" class="col-sm-2 col-form-label">Nueva Contraseña</label>
                                    <div class="col-sm-10">
                                        <input type="password" class="form-control" id="new_password"
                                               name="new_password" required minlength="8">
                                        <small class="form-text text-muted">Mínimo 8 caracteres</small>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="new_password_confirmation" class="col-sm-2 col-form-label">Confirmar Contraseña</label>
                                    <div class="col-sm-10">
                                        <input type="password" class="form-control" id="new_password_confirmation"
                                               name="new_password_confirmation" required minlength="8">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="offset-sm-2 col-sm-10">
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-key"></i> Cambiar Contraseña
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Profile form submission
    $('#profileForm').submit(function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('{{ route("profile.update") }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success('Perfil actualizado correctamente');
                // Update navbar name if changed
                if (data.user && data.user.name) {
                    $('.navbar .dropdown-header').text(data.user.name);
                }
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(key => {
                        toastr.error(data.errors[key][0]);
                    });
                } else {
                    toastr.error(data.message || 'Error al actualizar el perfil');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('Error al actualizar el perfil');
        });
    });


    // Password form submission
    $('#passwordForm').submit(function(e) {
        e.preventDefault();

        if ($('#new_password').val() !== $('#new_password_confirmation').val()) {
            toastr.error('Las contraseñas no coinciden');
            return;
        }

        const formData = new FormData(this);

        fetch('{{ route("profile.password") }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success('Contraseña cambiada correctamente');
                $('#passwordForm')[0].reset();
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(key => {
                        toastr.error(data.errors[key][0]);
                    });
                } else {
                    toastr.error(data.message || 'Error al cambiar la contraseña');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('Error al cambiar la contraseña');
        });
    });
});
</script>
@endpush
