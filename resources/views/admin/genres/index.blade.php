@extends('layouts.admin')

@section('title', 'Géneros')

@section('toolbar')
<div class="page-title d-flex flex-column justify-content-center me-3">
    <h1 class="page-heading fw-bold fs-3 m-0">Géneros</h1>
</div>
@can('create genres')
    <div><a href="{{ route('admin.genres.create') }}" class="btn btn-primary">Nuevo género</a></div>
@endcan
@endsection

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->has('genre'))<div class="alert alert-danger">{{ $errors->first('genre') }}</div>@endif
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-row-dashed gy-5">
                <thead><tr><th>Nombre</th><th>Slug</th><th>Series</th><th>Estado</th><th></th></tr></thead>
                <tbody>
                @forelse($genres as $genre)
                    <tr>
                        <td>{{ $genre->name }}</td>
                        <td>{{ $genre->slug }}</td>
                        <td>{{ $genre->series_count }}</td>
                        <td><span class="badge {{ $genre->is_active ? 'badge-light-success' : 'badge-light-danger' }}">{{ $genre->is_active ? 'Activo' : 'Inactivo' }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('admin.genres.show', $genre) }}" class="btn btn-sm btn-light">Ver</a>
                            @can('edit genres')
                                <a href="{{ route('admin.genres.edit', $genre) }}" class="btn btn-sm btn-light-primary">Editar</a>
                            @endcan
                            @can('delete genres')
                                <form class="d-inline" method="POST" action="{{ route('admin.genres.destroy', $genre) }}" onsubmit="return confirm('¿Eliminar género?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-light-danger" type="submit">Eliminar</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">Sin generos</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $genres->links() }}
    </div>
</div>
@endsection
