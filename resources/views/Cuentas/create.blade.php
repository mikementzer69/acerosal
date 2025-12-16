@extends('layouts.app')

@section('content')

<div class="erp-section">

    <h2 class="erp-title">
        <i class="fa-solid fa-book"></i> Nueva Cuenta Contable
    </h2>

    {{-- ALERTA --}}
    @if ($errors->any())
        <div class="form-alert" style="background:#ffdddd; color:#a20000;">
            <strong>Errores detectados:</strong>
            <ul style="margin:0; padding-left:18px;">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORMULARIO --}}
    <div class="erp-table-container" style="padding:20px; max-width:600px; margin:auto;">

        <form method="POST" action="{{ route('cuentas.store') }}">
            @csrf

            <table class="erp-table" style="border:none;">
                <tr>
                    <td><strong>Código</strong></td>
                    <td>
                        <input type="text" name="codigo" class="search-input" required>
                    </td>
                </tr>

                <tr>
                    <td><strong>Nombre</strong></td>
                    <td>
                        <input type="text" name="nombre" class="search-input" required>
                    </td>
                </tr>

                <tr>
                    <td><strong>Tipo</strong></td>
                    <td>
                        <select name="tipo" class="search-input" required>
                            <option value="activo">Activo</option>
                            <option value="pasivo">Pasivo</option>
                            <option value="patrimonio">Patrimonio</option>
                            <option value="ingreso">Ingreso</option>
                            <option value="gasto">Gasto</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td><strong>Cuenta Padre</strong></td>
                    <td>
                        <select name="parent_id" class="search-input">
                            <option value="">Ninguna</option>
                            @foreach($cuentas_padre as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->codigo }} — {{ $p->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                </tr>

                <tr>
                    <td><strong>Movimiento</strong></td>
                    <td>
                        <input type="checkbox" name="es_movimiento" checked>
                        <span style="margin-left:5px;">Cuenta de movimiento</span>
                    </td>
                </tr>

                <tr>
                    <td><strong>Activa</strong></td>
                    <td>
                        <input type="checkbox" name="activo" checked>
                        <span style="margin-left:5px;">Activa</span>
                    </td>
                </tr>
            </table>

            <div class="erp-actions" style="text-align:center; margin-top:20px;">
                <button class="btn-primary" style="padding:8px 18px;">
                    GUARDAR
                </button>
                <a href="{{ route('cuentas.index') }}" class="btn-secondary" style="margin-left:8px;">
                    Cancelar
                </a>
            </div>

        </form>

    </div>

</div>

@endsection
