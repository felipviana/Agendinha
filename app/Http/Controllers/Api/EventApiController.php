<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\WorkType;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;

class EventApiController extends Controller
{
    private function userOwnsWorkType(Request $request, int $workTypeId): bool
    {
        return WorkType::where('id', $workTypeId)
            ->where('user_id', $request->user()->id)
            ->exists();
    }

    private function hasTimeConflict(
        int $userId,
        string $scheduledDate,
        string $startTime,
        ?string $endTime = null,
        ?int $ignoreEventId = null
    ): bool {
        $newStart = Carbon::createFromFormat('H:i', $startTime);

        // Se não informar hora final, assume mesmo horário inicial
        $newEnd = $endTime
            ? Carbon::createFromFormat('H:i', $endTime)
            : Carbon::createFromFormat('H:i', $startTime);

        $query = Event::where('user_id', $userId)
            ->where('scheduled_date', $scheduledDate)
            ->whereNotNull('start_time');

        if ($ignoreEventId) {
            $query->where('id', '!=', $ignoreEventId);
        }

        $events = $query->get();

        foreach ($events as $event) {
            if (!$event->start_time) {
                continue;
            }

            $existingStart = Carbon::createFromFormat('H:i:s', $event->start_time);
            $existingEnd = $event->end_time
                ? Carbon::createFromFormat('H:i:s', $event->end_time)
                : Carbon::createFromFormat('H:i:s', $event->start_time);

            // normaliza para H:i caso algum venha com segundos
            $existingStart = Carbon::createFromFormat('H:i', $existingStart->format('H:i'));
            $existingEnd = Carbon::createFromFormat('H:i', $existingEnd->format('H:i'));

            $hasOverlap = $newStart->lt($existingEnd) && $newEnd->gt($existingStart);

            // também cobre caso ambos sejam exatamente iguais e sem intervalo
            $sameInstant = $newStart->equalTo($existingStart) && $newEnd->equalTo($existingEnd);

            if ($hasOverlap || $sameInstant) {
                return true;
            }
        }

        return false;
    }

    public function index(Request $request)
    {
        $events = Event::with('workType')
            ->where('user_id', $request->user()->id)
            ->orderBy('scheduled_date')
            ->orderBy('start_time')
            ->get();

        return response()->json($events, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'work_type_id' => 'required|exists:work_types,id',
            'schedule_type' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'scheduled_date' => 'nullable|date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'location' => 'required|string|max:255',
            'contractor_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string|max:255',
            'fee' => 'nullable|numeric',
            'is_recurring' => 'nullable',
            'recurrence_days' => 'nullable|array',
            'recurrence_days.*' => 'in:0,1,2,3,4,5,6',
            'recurrence_start_date' => 'nullable|date',
            'recurrence_end_date' => 'nullable|date|after_or_equal:recurrence_start_date',
        ]);

        if (! $this->userOwnsWorkType($request, (int) $request->work_type_id)) {
            return response()->json([
                'errors' => [
                    'work_type_id' => ['Tipo de trabalho inválido para este usuário.']
                ]
            ], 422);
        }

        if ($request->schedule_type === 'evento' && empty($request->start_time)) {
            return response()->json([
                'errors' => [
                    'start_time' => ['A hora inicial é obrigatória para eventos.']
                ]
            ], 422);
        }

        $isRecurring = $request->has('is_recurring');

        if (! $isRecurring) {
            if (
                $request->schedule_type === 'evento' &&
                $request->scheduled_date &&
                $request->start_time &&
                $this->hasTimeConflict(
                    $request->user()->id,
                    $request->scheduled_date,
                    $request->start_time,
                    $request->end_time
                )
            ) {
                return response()->json([
                    'errors' => [
                        'start_time' => ['Já existe um agendamento conflitante para esse horário.']
                    ]
                ], 422);
            }

            $event = Event::create([
                'user_id' => $request->user()->id,
                'work_type_id' => $request->work_type_id,
                'schedule_type' => $request->schedule_type,
                'title' => $request->title,
                'scheduled_date' => $request->scheduled_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'location' => $request->location,
                'contractor_name' => $request->contractor_name,
                'description' => $request->description,
                'status' => $request->status,
                'fee' => $request->fee,
                'is_recurring' => false,
                'recurrence_group' => null,
                'recurrence_days' => null,
                'recurrence_start_date' => null,
                'recurrence_end_date' => null,
            ]);

            return response()->json([
                'message' => 'Agendamento cadastrado com sucesso.',
                'data' => $event->load('workType'),
            ], 201);
        }

        if (empty($request->recurrence_days)) {
            return response()->json([
                'errors' => [
                    'recurrence_days' => ['Selecione pelo menos um dia da semana para o agendamento recorrente.']
                ]
            ], 422);
        }

        if (! $request->recurrence_start_date) {
            return response()->json([
                'errors' => [
                    'recurrence_start_date' => ['Informe a data inicial da recorrência.']
                ]
            ], 422);
        }

        if (! $request->recurrence_end_date) {
            return response()->json([
                'errors' => [
                    'recurrence_end_date' => ['Informe a data final da recorrência.']
                ]
            ], 422);
        }

        $startDate = Carbon::parse($request->recurrence_start_date);
        $endDate = Carbon::parse($request->recurrence_end_date);

        $selectedDays = array_map('intval', $request->recurrence_days);
        $recurrenceGroup = (string) Str::uuid();

        $currentDate = $startDate->copy();
        $createdCount = 0;

        while ($currentDate->lte($endDate)) {
            if (in_array($currentDate->dayOfWeek, $selectedDays)) {
                if (
                    $request->schedule_type === 'evento' &&
                    $request->start_time &&
                    $this->hasTimeConflict(
                        $request->user()->id,
                        $currentDate->toDateString(),
                        $request->start_time,
                        $request->end_time
                    )
                ) {
                    return response()->json([
                        'errors' => [
                            'start_time' => ['Já existe um agendamento conflitante para pelo menos uma das datas/horários informados.']
                        ]
                    ], 422);
                }

                Event::create([
                    'user_id' => $request->user()->id,
                    'work_type_id' => $request->work_type_id,
                    'schedule_type' => $request->schedule_type,
                    'title' => $request->title,
                    'scheduled_date' => $currentDate->toDateString(),
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'location' => $request->location,
                    'contractor_name' => $request->contractor_name,
                    'description' => $request->description,
                    'status' => $request->status,
                    'fee' => $request->fee,
                    'is_recurring' => true,
                    'recurrence_group' => $recurrenceGroup,
                    'recurrence_days' => implode(',', $request->recurrence_days),
                    'recurrence_start_date' => $request->recurrence_start_date,
                    'recurrence_end_date' => $request->recurrence_end_date,
                ]);

                $createdCount++;
            }

            $currentDate->addDay();
        }

        if ($createdCount === 0) {
            return response()->json([
                'errors' => [
                    'recurrence_days' => ['Nenhuma ocorrência foi gerada com os dias e período informados.']
                ]
            ], 422);
        }

        return response()->json([
            'message' => "Agendamento cadastrado com {$createdCount} ocorrência(s)."
        ], 201);
    }

    public function show(Request $request, Event $event)
    {
        if ($event->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Acesso não autorizado.'
            ], 403);
        }

        return response()->json($event->load('workType'), 200);
    }

    public function update(Request $request, Event $event)
    {
        if ($event->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Acesso não autorizado.'
            ], 403);
        }

        $request->validate([
            'work_type_id' => 'required|exists:work_types,id',
            'schedule_type' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'scheduled_date' => 'required|date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'location' => 'required|string|max:255',
            'contractor_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string|max:255',
            'fee' => 'nullable|numeric',
        ]);

        if (! $this->userOwnsWorkType($request, (int) $request->work_type_id)) {
            return response()->json([
                'errors' => [
                    'work_type_id' => ['Tipo de trabalho inválido para este usuário.']
                ]
            ], 422);
        }

        if ($request->schedule_type === 'evento' && empty($request->start_time)) {
            return response()->json([
                'errors' => [
                    'start_time' => ['A hora inicial é obrigatória para eventos.']
                ]
            ], 422);
        }

        if (
            $request->schedule_type === 'evento' &&
            $request->scheduled_date &&
            $request->start_time &&
            $this->hasTimeConflict(
                $request->user()->id,
                $request->scheduled_date,
                $request->start_time,
                $request->end_time,
                $event->id
            )
        ) {
            return response()->json([
                'errors' => [
                    'start_time' => ['Já existe um agendamento conflitante para esse horário.']
                ]
            ], 422);
        }

        $event->update([
            'work_type_id' => $request->work_type_id,
            'schedule_type' => $request->schedule_type,
            'title' => $request->title,
            'scheduled_date' => $request->scheduled_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location' => $request->location,
            'contractor_name' => $request->contractor_name,
            'description' => $request->description,
            'status' => $request->status,
            'fee' => $request->fee,
        ]);

        return response()->json([
            'message' => 'Agendamento atualizado com sucesso.',
            'data' => $event->load('workType'),
        ], 200);
    }

    public function destroy(Request $request, Event $event)
    {
        if ($event->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Acesso não autorizado.'
            ], 403);
        }

        $event->delete();

        return response()->json([
            'message' => 'Agendamento deletado com sucesso.'
        ], 200);
    }

    public function destroySeries(Request $request, Event $event)
    {
        if ($event->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Acesso não autorizado.'
            ], 403);
        }

        if (! $event->is_recurring || ! $event->recurrence_group) {
            return response()->json([
                'message' => 'Este evento não pertence a uma série recorrente.'
            ], 422);
        }

        Event::where('user_id', $request->user()->id)
            ->where('recurrence_group', $event->recurrence_group)
            ->delete();

        return response()->json([
            'message' => 'Série de agendamentos excluída com sucesso.'
        ], 200);
    }

    public function moveDate(Request $request, Event $event)
    {
        if ($event->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Acesso não autorizado.'
            ], 403);
        }

        $request->validate([
            'scheduled_date' => 'required|date',
        ]);

        if (
            $event->schedule_type === 'evento' &&
            $event->start_time &&
            $this->hasTimeConflict(
                $request->user()->id,
                $request->scheduled_date,
                Carbon::createFromFormat('H:i:s', $event->start_time)->format('H:i'),
                $event->end_time ? Carbon::createFromFormat('H:i:s', $event->end_time)->format('H:i') : null,
                $event->id
            )
        ) {
            return response()->json([
                'errors' => [
                    'scheduled_date' => ['Já existe um agendamento conflitante para essa nova data/horário.']
                ]
            ], 422);
        }

        $event->update([
            'scheduled_date' => $request->scheduled_date,
        ]);

        return response()->json([
            'message' => 'Data do agendamento atualizada com sucesso.',
            'data' => $event->load('workType'),
        ], 200);
    }

    public function dashboard(Request $request)
    {
        $userId = $request->user()->id;

        $totalEvents = Event::where('user_id', $userId)->count();

        $confirmedEvents = Event::where('user_id', $userId)
            ->where('status', 'confirmado')
            ->count();

        $pendingEvents = Event::where('user_id', $userId)
            ->where('status', 'pendente')
            ->count();

        $recurringEvents = Event::where('user_id', $userId)
            ->where('is_recurring', true)
            ->count();

        $currentMonthRevenue = Event::where('user_id', $userId)
            ->whereMonth('scheduled_date', now()->month)
            ->whereYear('scheduled_date', now()->year)
            ->where('status', 'confirmado')
            ->sum('fee');

        $currentMonthForecast = Event::where('user_id', $userId)
            ->whereMonth('scheduled_date', now()->month)
            ->whereYear('scheduled_date', now()->year)
            ->whereIn('status', ['confirmado', 'pendente'])
            ->sum('fee');

        $upcomingEvents = Event::with('workType')
            ->where('user_id', $userId)
            ->whereDate('scheduled_date', '>=', now()->toDateString())
            ->orderBy('scheduled_date')
            ->orderBy('start_time')
            ->take(5)
            ->get();

        return response()->json([
            'total_events' => $totalEvents,
            'confirmed_events' => $confirmedEvents,
            'pending_events' => $pendingEvents,
            'recurring_events' => $recurringEvents,
            'current_month_revenue' => $currentMonthRevenue,
            'current_month_forecast' => $currentMonthForecast,
            'upcoming_events' => $upcomingEvents,
        ], 200);
    }
}