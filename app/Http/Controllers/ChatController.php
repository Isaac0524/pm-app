<?php

namespace App\Http\Controllers;

use App\Services\AIClientService;

use Illuminate\Http\Request;

class ChatController extends Controller
{
    protected $aiService;

    public function __construct(AIClientService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'context' => 'array',
        ]);

        $response = $this->aiService->sendChatMessage(
            $request->message,
            $request->context ?? []
        );

        return response()->json($response);
    }

    public function createProject(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
        ]);

        $response = $this->aiService->createProjectViaChat(
            $request->title,
            $request->description
        );

        return response()->json($response);
    }

    public function addTask(Request $request)
    {
        $request->validate([
            'task_title' => 'required|string|max:255',
            'project_name' => 'required|string|max:255',
            'context' => 'nullable|string|max:1000',
        ]);

        $response = $this->aiService->addTaskViaChat(
            $request->task_title,
            $request->project_name,
            $request->context ?? ''
        );

        return response()->json($response);
    }

    public function modifyProject(Request $request)
    {
        $request->validate([
            'project_name' => 'required|string|max:255',
            'modification' => 'required|string|max:1000',
        ]);

        $response = $this->aiService->modifyProjectViaChat(
            $request->project_name,
            $request->modification
        );

        return response()->json($response);
    }
}
