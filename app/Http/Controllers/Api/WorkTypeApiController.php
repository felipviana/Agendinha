<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkType;
use Illuminate\Http\Request;
use App\Models\Event;

class WorkTypeApiController extends Controller
{
    public function index(Request $request)
    {
        $workTypes = WorkType::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($workTypes, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
        ]);

        $alreadyExists = WorkType::where('user_id', $request->user()->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($validated['name'])])
            ->exists();

        if ($alreadyExists) {
            return response()->json([
                'errors' => [
                    'name' => ['Você já possui um tipo de trabalho com esse nome.']
                ]
            ], 422);
        }

        $workType = WorkType::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'],
        ]);

        return response()->json([
            'message' => 'Tipo de trabalho criado com sucesso.',
            'data' => $workType
        ], 201);
    }

    public function show(Request $request, WorkType $workType)
    {
        if ($workType->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Acesso não autorizado',
            ], 403);
        }

        return response()->json($workType, 200);
    }

    public function update(Request $request, WorkType $workType)
    {

        if ($workType->user_id !== $request->user()->id) {
            return response()->json([
                'message' => "Acesso não autorizado",
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
        ]);

        $alreadyExists = WorkType::where('user_id', $request->user()->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($validated['name'])])
            ->where('id', '!=', $workType->id)
            ->exists();

        if ($alreadyExists) {
            return response()->json([
                'errors' => [
                    'name' => ['Você já possui um tipo de trabalho com esse nome.']
                ]
            ], 422);
        }

        $workType->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'],
        ]);

        return response()->json([
            'message' => 'Tipo de trabalho atualizado com sucesso.',
            'data' => $workType
        ], 200);
    }

    public function destroy(Request $request, WorkType $workType)
    {
        if ($workType->user_id !== $request->user()->id) {
            return response()->json([
                'message' => "Acesso não autorizado",
            ], 403);
        }

        $hasLinkedEvents = Event::where('user_id', $request->user()->id)
            ->where('work_type_id', $workType->id)
            ->exists();

        if ($hasLinkedEvents) {
            return response()->json([
                'message' => 'Este tipo de trabalho não pode ser excluído porque está vinculado a agendamentos.'
            ], 422);
        }

        $workType->delete();

        return response()->json([
            'message' => 'Tipo de trabalho excluído com sucesso.'
        ], 200);
    }
}
