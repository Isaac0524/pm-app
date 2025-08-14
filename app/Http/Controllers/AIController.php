<?php

namespace App\Http\Controllers;

use App\Services\AIClientService;
use App\Models\Project;
use App\Models\Activity;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Ajouté

class AIController extends Controller
{
    use AuthorizesRequests; // Ajouté

    protected $aiService;

    public function __construct(AIClientService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function analyzeProject(Request $request, Project $project)
    {
        $this->authorize('manager', $project);

        $analysis = $this->aiService->analyzeProject(
            $project->title,
            $project->description
        );

        return response()->json($analysis);
    }

    public function generateActivities(Project $project)
    {
        $this->authorize('manager', $project);

        $analysis = $this->aiService->analyzeProject(
            $project->title,
            $project->description
        );

        return response()->json($analysis);
    }

    public function generateTasks(Activity $activity)
    {
        $this->authorize('manager', $activity->project);

        $analysis = $this->aiService->generateTasks($activity->id);

        return response()->json($analysis);
    }

    public function chatAddTask(Request $request)
    {
        $request->validate([
            'task_title' => 'required|string|max:255',
            'project_name' => 'required|string|max:255',
            'context' => 'nullable|string',
        ]);

        $result = $this->aiService->addTaskViaChat(
            $request->task_title,
            $request->project_name,
            $request->context ?? ''
        );

        return response()->json($result);
    }
}
