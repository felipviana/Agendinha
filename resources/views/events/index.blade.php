@extends('layouts.app')

@section('content')
    <h2>Agendamentos</h2>

    <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
        <a href="{{ route('events.index', ['view' => 'list']) }}" class="btn {{ $view === 'list' ? 'btn-primary' : 'btn-secondary' }}">
            Modo Lista
        </a>

        <a href="{{ route('events.index', ['view' => 'calendar']) }}" class="btn {{ $view === 'calendar' ? 'btn-primary' : 'btn-secondary' }}">
            Modo Calendário
        </a>

        <a href="{{ route('events.create') }}" class="btn btn-primary">Novo Agendamento</a>
    </div>

    @if($view === 'list')
        @if($events->isEmpty())
            <p style="margin-top: 20px;">Nenhum agendamento cadastrado.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Tipo de Trabalho</th>
                        <th>Tipo</th>
                        <th>Recorrência</th>
                        <th>Data</th>
                        <th>Horário</th>
                        <th>Local</th>
                        <th>Status</th>
                        <th>Cachê</th>
                        <th style="width: 260px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $event)
                        <tr>
                            <td>{{ $event->title }}</td>

                            <td>
                                @if($event->workType)
                                    <span style="
                                        display: inline-flex;
                                        align-items: center;
                                        gap: 8px;
                                        background: {{ $event->workType->color }};
                                        color: #ffffff;
                                        padding: 6px 10px;
                                        border-radius: 999px;
                                        font-size: 12px;
                                        font-weight: bold;
                                    ">
                                        <span style="
                                            display: inline-block;
                                            width: 10px;
                                            height: 10px;
                                            background: rgba(255,255,255,0.85);
                                            border-radius: 50%;
                                        "></span>
                                        {{ $event->workType->name }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>

                            <td>{{ ucfirst($event->schedule_type) }}</td>

                            <td>
                                @if($event->is_recurring)
                                    @php
                                        $daysMap = [
                                            '0' => 'Dom',
                                            '1' => 'Seg',
                                            '2' => 'Ter',
                                            '3' => 'Qua',
                                            '4' => 'Qui',
                                            '5' => 'Sex',
                                            '6' => 'Sáb',
                                        ];

                                        $days = $event->recurrence_days ? explode(',', $event->recurrence_days) : [];
                                        $translatedDays = [];

                                        foreach ($days as $day) {
                                            if (isset($daysMap[$day])) {
                                                $translatedDays[] = $daysMap[$day];
                                            }
                                        }
                                    @endphp

                                    <span class="badge badge-blue">Fixo</span>
                                    <br>
                                    <small>{{ implode(', ', $translatedDays) }}</small>
                                @else
                                    <span class="badge badge-yellow">Avulso</span>
                                @endif
                            </td>

                            <td>{{ \Carbon\Carbon::parse($event->scheduled_date)->format('d/m/Y') }}</td>

                            <td>
                                {{ $event->start_time }}
                                @if($event->end_time)
                                    - {{ $event->end_time }}
                                @endif
                            </td>

                            <td>{{ $event->location }}</td>

                            <td>
                                @if($event->status === 'confirmado')
                                    <span class="badge badge-green">Confirmado</span>
                                @elseif($event->status === 'pendente')
                                    <span class="badge badge-yellow">Pendente</span>
                                @elseif($event->status === 'cancelado')
                                    <span class="badge badge-red">Cancelado</span>
                                @else
                                    <span class="badge badge-blue">{{ ucfirst($event->status) }}</span>
                                @endif
                            </td>

                            <td>
                                @if($event->fee)
                                    R$ {{ number_format($event->fee, 2, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="actions">
                                <a href="{{ route('events.edit', $event->id) }}" class="btn btn-warning">Editar</a>

                                <form action="{{ route('events.destroy', $event->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Deseja excluir este agendamento?')">
                                        Excluir
                                    </button>
                                </form>

                                @if($event->is_recurring)
                                    <form action="{{ route('events.destroySeries', $event->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-secondary" onclick="return confirm('Deseja excluir toda a série deste agendamento?')">
                                            Excluir Série
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @else
        @php
            $startOfMonth = $calendarDate->copy()->startOfMonth();
            $endOfMonth = $calendarDate->copy()->endOfMonth();
            $startCalendar = $startOfMonth->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
            $endCalendar = $endOfMonth->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
            $currentDate = $startCalendar->copy();

            $weekDays = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

            $previousMonth = $calendarDate->copy()->subMonth();
            $nextMonth = $calendarDate->copy()->addMonth();
        @endphp
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <a href="{{ route('events.index', ['view' => 'calendar', 'month' => $previousMonth->month, 'year' => $previousMonth->year]) }}"
            class="btn btn-secondary">
                ← Mês Anterior
            </a>

            <h3 style="margin: 0; color: #1e3a8a;">
                {{ $calendarDate->translatedFormat('F \d\e Y') }}
            </h3>

            <a href="{{ route('events.index', ['view' => 'calendar', 'month' => $nextMonth->month, 'year' => $nextMonth->year]) }}"
            class="btn btn-secondary">
                Próximo Mês →
            </a>
        </div>
        <div class="calendar-grid">
            <div class="calendar-header">
                @foreach($weekDays as $dayName)
                    <div class="calendar-weekday">{{ $dayName }}</div>
                @endforeach
            </div>

            <div class="calendar-body">
                @while($currentDate->lte($endCalendar))
                    @php
                        $dateString = $currentDate->toDateString();
                        $dayEvents = $calendarEvents[$dateString] ?? [];
                        $isCurrentMonth = $currentDate->month === $calendarDate->month;
                    @endphp

                    <div class="calendar-day {{ $isCurrentMonth ? '' : 'calendar-day-muted' }}">
                        <div class="calendar-date">{{ $currentDate->day }}</div>

                        @foreach($dayEvents as $event)
                            <div class="calendar-event" style="background: {{ $event->workType->color ?? '#3B82F6' }};">
                                <strong>{{ $event->title }}</strong><br>
                                <small>{{ $event->start_time }}</small>
                            </div>
                        @endforeach
                    </div>

                    @php
                        $currentDate->addDay();
                    @endphp
                @endwhile
            </div>
        </div>
    @endif
@endsection