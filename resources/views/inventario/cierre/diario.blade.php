@extends('layouts.app')

@section('content')
<style>
input[type="date"] {
    color: #ffffff !important; /* Texto en blanco */
    color-scheme: dark;       /* Avisa al navegador que use el motor oscuro nativo */
}

/* Hacer que el icono del calendario sea blanco y visible */
input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(1);        /* Invierte el color del icono (de negro a blanco) */
    cursor: pointer;          /* Cambia el cursor para que el usuario sepa que puede hacer clic */
}
</style>

<div class="form-container">

    <h2 class="form-title">
        <i class="fa-solid fa-calendar-day"></i> Cierre Diario de Inventario
    </h2>

    @if(session('msg'))
        <div class="form-alert">{{ session('msg') }}</div>
    @endif

    @if(session('error'))
        <div class="form-alert alert-error">{{ session('error') }}</div>
    @endif

<form id="formCierre" method="POST" action="{{ route('inventario.cierre.diario.ejecutar') }}">
@csrf
        <div class="form-group">
            <label>Empresa de Cierre</label>
            <input type="text" class="form-control bg-dark text-white border-secondary"
                value="{{ $nombreEmpresa }}">

            <input type="hidden" name="id_empresa" value="{{ $idEmpresa }}">
        </div>

        <div class="form-group">
            <label>Fecha *</label>
            <input type="date" name="fecha" value="{{ $fechaSugerida }}" required>
        </div>

        <div class="form-actions">
            <button
                class="btn-primary"
                type="submit"
                onclick="return confirm('¿Estás seguro de ejecutar el cierre para esta fecha? Esta acción actualizará los saldos históricos de Acerosal.')">
                <i class="fa-solid fa-lock me-2"></i> Ejecutar Cierre
            </button>
        </div>

    </form>

<hr style="border-top: 1px solid #334155; margin: 25px 0;">

    <div class="maintenance-actions" style="text-align: center;">
        <p style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 10px;">
            <i class="fa-solid fa-screwdriver-wrench"></i> Herramientas de Mantenimiento
        </p>
        <button
            type="button"
            class="btn-secondary"
            style="background-color: #92400e; border: none; padding: 10px 20px; border-radius: 6px; color: white; cursor: pointer;"
            onclick="confirmarReconstruccion()">
            <i class="fa-solid fa-arrows-rotate me-2"></i> Reconstruir Saldos (Piezas/Lotes/Prod)
        </button>
    </div>
</div>


<script>
function confirmarReconstruccion() {
    // Obtenemos la fecha que el usuario tiene seleccionada en el input de arriba
    const fechaSeleccionada = document.querySelector('input[name="fecha"]').value;

    Swal.fire({
        title: '¿Reconstruir Inventario?',
        text: `Se recalcularán piezas, lotes y productos desde el ${fechaSeleccionada}. Este proceso puede tardar unos segundos.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#92400e', // Color ámbar/marrón para advertencia
        cancelButtonColor: '#475569',
        confirmButtonText: 'Sí, reconstruir ahora',
        cancelButtonText: 'Cancelar',
        background: '#1e293b',
        color: '#fff'
    }).then((result) => {
        if (result.isConfirmed) {
            ejecutarReconstruccion(fechaSeleccionada);
        }
    });
}

function ejecutarReconstruccion(fecha) {
    // Mostramos un loader para que el usuario no desespere
    Swal.fire({
        title: 'Procesando...',
        text: 'Se está ajustando los saldos del inventario.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
        background: '#1e293b',
        color: '#fff'
    });

    fetch("{{ route('inventario.reconstruir') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ fecha_inicio: fecha })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: data.message,
                background: '#1e293b',
                color: '#fff',
                confirmButtonColor: '#2563eb'
            }).then(() => {
                location.reload(); // Recargamos para ver los saldos nuevos
            });
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error Crítico',
            text: 'No se pudo completar la reconstrucción: ' + error.message,
            background: '#1e293b',
            color: '#fff'
        });
    });
}
</script>


</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    @if(session('msg'))
        Swal.fire({
            icon: 'success',
            title: '¡Cierre Completado!',
            text: "{{ session('msg') }}",
            background: '#1e293b', // Fondo oscuro Acerosal
            color: '#fff',
            confirmButtonColor: '#2563eb',
            timer: 4000
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error en el Cierre',
            text: "{{ session('error') }}",
            background: '#1e293b',
            color: '#fff',
            confirmButtonColor: '#dc2626'
        });
    @endif
</script>

<script>
@if(session('confirmar_sobreescritura'))
    setTimeout(function() {
        if (confirm("⚠️ Ya existe un cierre. ¿Deseas sobreescribir los datos de Acerosal?")) {
            let form = document.getElementById('formCierre');
            if (form) {
                // Creamos un input oculto real para que Laravel lo vea sí o sí
                let hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'confirmado'; // Este nombre debe coincidir con el controlador
                hiddenInput.value = '1';
                form.appendChild(hiddenInput);

                form.submit(); // Enviamos
            } else {
                alert("Error: El formulario con ID 'formCierre' no existe en el HTML.");
            }
        }
    }, 250);
@endif
</script>
@endsection
