@extends('layouts.app')

@section('content')
    <h2>Dashboard</h2>

    <div class="dashboard-grid">
        <div class="stat-card">
            <h3>Total de Agendamentos</h3>
            <p>{{ $totalEvents }}</p>
        </div>

        <div class="stat-card">
            <h3>Confirmados</h3>
            <p>{{ $confirmedEvents }}</p>
        </div>

        <div class="stat-card">
            <h3>Pendentes</h3>
            <p>{{ $pendingEvents }}</p>
        </div>

        <div class="stat-card">
            <h3>Fixos</h3>
            <p>{{ $recurringEvents }}</p>
        </div>

        <div class="stat-card">
            <h3>Valor Confirmado do Mês</h3>
            <p>R$ {{ number_format($currentMonthRevenue, 2, ',', '.') }}</p>
        </div>

        <div class="stat-card">
            <h3>Valor Esperado do Mês</h3>
            <p>R$ {{ number_format($currentMonthForecast, 2, ',', '.') }}</p>
        </div>
    </div>

    <div class="section-subtitle">Próximos Agendamentos</div>

    @if($upcomingEvents->isEmpty())
        <p>Nenhum agendamento futuro encontrado.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Tipo de Trabalho</th>
                    <th>Data</th>
                    <th>Horário</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($upcomingEvents as $event)
                    <tr>
                        <td>{{ $event->title }}</td>
                        <td>{{ $event->workType->name ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($event->scheduled_date)->format('d/m/Y') }}</td>
                        <td>
                            {{ $event->start_time }}
                            @if($event->end_time)
                                - {{ $event->end_time }}
                            @endif
                        </td>
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
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection