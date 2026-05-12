@extends('layouts.app')

@section('content')
    <h2>Tipos de Trabalho</h2>

    <a href="{{ route('work-types.create') }}" class="btn btn-primary">Novo Tipo de Trabalho</a>

    @if($workTypes->isEmpty())
        <p style="margin-top: 20px;">Nenhum tipo de trabalho cadastrado.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Cor</th>
                    <th style="width: 220px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($workTypes as $workType)
                    <tr>
                        <td>{{ $workType->name }}</td>
                        <td>{{ $workType->description ?: '-' }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="display:inline-block; width:18px; height:18px; border-radius:50%; background: {{ $workType->color }}; border:1px solid #ccc;"></span>
                                <span>{{ $workType->color }}</span>
                            </div>
                        </td>
                        <td class="actions">
                            <a href="{{ route('work-types.edit', $workType->id) }}" class="btn btn-warning">Editar</a>

                            <form action="{{ route('work-types.destroy', $workType->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Deseja excluir este tipo de trabalho?')">
                                    Excluir
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection