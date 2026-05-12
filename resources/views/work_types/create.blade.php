@extends('layouts.app')

@section('content')
    <h2>Novo Tipo de Trabalho</h2>

    <form id="workTypeForm" action="{{ route('work-types.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name">Nome</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required>
        </div>

        <div class="form-group">
            <label for="description">Descrição</label>
            <textarea name="description" id="description" rows="4">{{ old('description') }}</textarea>
        </div>
        
        <div class="form-group">
            <label for="color">Cor do Tipo de Trabalho</label>
            <input type="color" name="color" id="color" value="{{ old('color', '#3B82F6') }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{ route('work-types.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
@endsection

<script>
    $("#workTypeForm").on("submit", function() {
        $(this).find("button[type='submit']").prop("disabled", true).text("Salvando...");
    });
</script>