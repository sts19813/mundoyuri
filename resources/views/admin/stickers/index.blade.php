@extends('layouts.admin')

@section('title', 'Stickers - Admin')

@section('toolbar')
    <div id="kt_app_page_title" class="page-title d-flex align-items-center flex-wrap me-3 mb-2">
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">Stickers</h1>
        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 ms-2">
            <li class="breadcrumb-item text-muted"><a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Admin</a></li>
            <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
            <li class="breadcrumb-item text-muted">Comunidad</li>
        </ul>
    </div>
@endsection

@section('content')
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card mb-7">
        <div class="card-body">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                <div>
                    <h2 class="fw-bold text-gray-900 mb-1">Crear sticker general</h2>
                    <p class="text-muted fs-7 mb-0">Súbelo aquí para publicarlo de inmediato a toda la comunidad. PNG, JPG, WebP o GIF; transparencias y animación se conservan.</p>
                </div>
                <form method="POST" action="{{ route('admin.stickers.store') }}" enctype="multipart/form-data" class="d-flex align-items-center gap-3">
                    @csrf
                    <input class="form-control form-control-sm" type="file" name="sticker" accept="image/png,image/jpeg,image/webp,image/gif" required>
                    <button class="btn btn-sm btn-primary text-nowrap" type="submit"><i class="ki-outline ki-plus fs-3"></i>Publicar</button>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title"><div><h2 class="fw-bold text-gray-900 mb-1">Catálogo y solicitudes</h2><div class="text-muted fs-7">Los stickers personales solo los ve su creadora hasta que los apruebes.</div></div></div>
        </div>
        <div class="card-body pt-4">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed gy-5">
                    <thead><tr class="text-start text-muted fw-bold fs-7 text-uppercase"><th>Sticker</th><th>Creado por</th><th>Estado</th><th>Aprobación</th><th class="text-end">Acciones</th></tr></thead>
                    <tbody>
                        @forelse($stickers as $sticker)
                            <tr>
                                <td><div class="d-flex align-items-center gap-3"><img src="{{ route('stickers.file', $sticker) }}" alt="" style="width:64px;height:64px;object-fit:contain"><div><div class="fw-bold text-gray-900">{{ $sticker->name }}</div><div class="text-muted fs-7">{{ number_format($sticker->size / 1024, 0) }} KB · {{ strtoupper(str_replace('image/', '', $sticker->mime)) }}</div></div></div></td>
                                <td>{{ $sticker->owner?->displayName() ?: 'Cuenta eliminada' }}</td>
                                <td><span class="badge {{ $sticker->is_general ? 'badge-light-success' : 'badge-light-warning' }}">{{ $sticker->is_general ? 'General' : 'Personal' }}</span></td>
                                <td>{{ $sticker->approved_at ? $sticker->approved_at->timezone('America/Merida')->format('d/m/Y g:i a') : 'Pendiente' }}</td>
                                <td class="text-end text-nowrap">
                                    @unless($sticker->is_general)
                                        <form method="POST" action="{{ route('admin.stickers.approve', $sticker) }}" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-light-primary me-1">Aprobar</button>
                                        </form>
                                    @endunless
                                    <form method="POST" action="{{ route('admin.stickers.destroy', $sticker) }}" class="d-inline" onsubmit="return confirm('¿Eliminar este sticker del catálogo? Los mensajes ya enviados no se alterarán.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-10">Aún no hay stickers. Crea el primero desde arriba.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $stickers->links() }}
        </div>
    </div>
@endsection
