@extends('layouts.app')

@section('content')
    <h2>Novo Agendamento</h2>

    <form action="{{ route('events.store') }}" method="POST">
        @csrf

        <div class="form-row">
            <div class="form-group">
                <label for="work_type_id">Tipo de Trabalho</label>
                <select name="work_type_id" id="work_type_id" required>
                    <option value="">Selecione</option>
                    @foreach($workTypes as $workType)
                        <option value="{{ $workType->id }}" {{ old('work_type_id') == $workType->id ? 'selected' : '' }}>
                            {{ $workType->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="schedule_type">Tipo de Agendamento</label>
                <select name="schedule_type" id="schedule_type" required>
                    <option value="">Selecione</option>
                    <option value="evento" {{ old('schedule_type') == 'evento' ? 'selected' : '' }}>Evento</option>
                    <option value="entrega" {{ old('schedule_type') == 'entrega' ? 'selected' : '' }}>Entrega</option>
                    <option value="retirada" {{ old('schedule_type') == 'retirada' ? 'selected' : '' }}>Retirada</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="title">Título</label>
            <input type="text" name="title" id="title" value="{{ old('title') }}" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="scheduled_date">Data</label>
                <input type="date" name="scheduled_date" id="scheduled_date" value="{{ old('scheduled_date') }}">
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status" required>
                    <option value="">Selecione</option>
                    <option value="pendente" {{ old('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                    <option value="confirmado" {{ old('status') == 'confirmado' ? 'selected' : '' }}>Confirmado</option>
                    <option value="cancelado" {{ old('status') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    <option value="concluido" {{ old('status') == 'concluido' ? 'selected' : '' }}>Concluído</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="start_time">Hora Inicial</label>
                <input type="time" name="start_time" id="start_time" value="{{ old('start_time') }}" required>
            </div>

            <div class="form-group">
                <label for="end_time">Hora Final</label>
                <input type="time" name="end_time" id="end_time" value="{{ old('end_time') }}">
            </div>
        </div>

        <div class="form-group">
            <label for="location">Local</label>
            <input type="text" name="location" id="location" value="{{ old('location') }}" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="contractor_name">Contratante</label>
                <input type="text" name="contractor_name" id="contractor_name" value="{{ old('contractor_name') }}">
            </div>

            <div class="form-group">
                <label for="fee">Cachê / Valor</label>
                <input type="number" step="0.01" name="fee" id="fee" value="{{ old('fee') }}">
            </div>
        </div>

        <div class="form-group">
            <label for="description">Descrição</label>
            <textarea name="description" id="description" rows="4">{{ old('description') }}</textarea>
        </div>

        <div class="section-title">Recorrência</div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }}>
                Agendamento fixo
            </label>
        </div>

        @php
            $selectedDays = old('recurrence_days', []);
        @endphp

        <div class="form-group checkbox-group">
            <label>Dias da semana</label>
            <label><input type="checkbox" name="recurrence_days[]" value="1" {{ in_array('1', $selectedDays) ? 'checked' : '' }}> Segunda</label>
            <label><input type="checkbox" name="recurrence_days[]" value="2" {{ in_array('2', $selectedDays) ? 'checked' : '' }}> Terça</label>
            <label><input type="checkbox" name="recurrence_days[]" value="3" {{ in_array('3', $selectedDays) ? 'checked' : '' }}> Quarta</label>
            <label><input type="checkbox" name="recurrence_days[]" value="4" {{ in_array('4', $selectedDays) ? 'checked' : '' }}> Quinta</label>
            <label><input type="checkbox" name="recurrence_days[]" value="5" {{ in_array('5', $selectedDays) ? 'checked' : '' }}> Sexta</label>
            <label><input type="checkbox" name="recurrence_days[]" value="6" {{ in_array('6', $selectedDays) ? 'checked' : '' }}> Sábado</label>
            <label><input type="checkbox" name="recurrence_days[]" value="0" {{ in_array('0', $selectedDays) ? 'checked' : '' }}> Domingo</label>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="recurrence_start_date">Data inicial da recorrência</label>
                <input type="date" name="recurrence_start_date" id="recurrence_start_date" value="{{ old('recurrence_start_date') }}">
            </div>

            <div class="form-group">
                <label for="recurrence_end_date">Data final da recorrência</label>
                <input type="date" name="recurrence_end_date" id="recurrence_end_date" value="{{ old('recurrence_end_date') }}">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="{{ route('events.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
@endsection