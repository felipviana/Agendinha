<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportApiController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->startOfDay()
            : now()->startOfDay();

        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : $endDate->copy()->subMonthsNoOverflow(5)->startOfMonth();

        if ($endDate->lt($startDate)) {
            return response()->json([
                'message' => 'A data final precisa ser igual ou posterior a data inicial.',
            ], 422);
        }

        $userId = $request->user()->id;

        $byWorkType = WorkType::query()
            ->where('work_types.user_id', $userId)
            ->leftJoin('events', function ($join) use ($userId, $startDate, $endDate) {
                $join->on('events.work_type_id', '=', 'work_types.id')
                    ->where('events.user_id', '=', $userId)
                    ->whereBetween('events.scheduled_date', [
                        $startDate->toDateString(),
                        $endDate->toDateString(),
                    ]);
            })
            ->select([
                'work_types.id',
                'work_types.name',
                'work_types.color',
            ])
            ->selectRaw(
                'COUNT(CASE WHEN events.status <> ? THEN events.id END) AS jobs_count,
                COALESCE(SUM(CASE WHEN events.status IN (?, ?) THEN COALESCE(events.fee, 0) ELSE 0 END), 0) AS revenue,
                COALESCE(SUM(CASE WHEN events.status IN (?, ?, ?) THEN COALESCE(events.fee, 0) ELSE 0 END), 0) AS forecast',
                ['cancelado', 'confirmado', 'concluido', 'confirmado', 'concluido', 'pendente']
            )
            ->groupBy('work_types.id', 'work_types.name', 'work_types.color')
            ->orderByDesc('revenue')
            ->orderByDesc('jobs_count')
            ->get()
            ->map(function ($workType) {
                return [
                    'id' => $workType->id,
                    'name' => $workType->name,
                    'color' => $workType->color,
                    'jobs_count' => (int) $workType->jobs_count,
                    'revenue' => (float) $workType->revenue,
                    'forecast' => (float) $workType->forecast,
                ];
            })
            ->values();

        return response()->json([
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'totals' => [
                'jobs_count' => $byWorkType->sum('jobs_count'),
                'revenue' => $byWorkType->sum('revenue'),
                'forecast' => $byWorkType->sum('forecast'),
                'active_work_types' => $byWorkType
                    ->filter(function ($workType) {
                        return $workType['jobs_count'] > 0;
                    })
                    ->count(),
            ],
            'by_work_type' => $byWorkType,
        ], 200);
    }
}
