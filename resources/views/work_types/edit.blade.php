@extends('layouts.app')

@section('content')
    <h2>Editar Tipo de Trabalho</h2>

    <form action="{{ route('work-types.update', $workType->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Nome</label>
            <input type="text" name="name" id="name" value="{{ old('name', $workType->name) }}" required>
        </div>

        <div class="form-group">
            <label for="description">Descrição</label>
            <textarea name="description" id="description" rows="4">{{ old('description', $workType->description) }}</textarea>
        </div>
        
        <div class="form-group">
            <label for="color">Cor do Tipo de Trabalho</label>
            <input type="color" name="color" id="color" value="{{ old('color', $workType->color ?? '#3B82F6') }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Atualizar</button>
        <a href="{{ route('work-types.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
@endsection