<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
class EnhancedAIClientService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('AI_SERVICE_URL', 'http://localhost:5000/api/ai');
    }

    public function analyzeProject($title, $description)
    {
        $response = Http::post("{$this->baseUrl}/analyze-project", [
            'title' => $title,
            'description' => $description,
        ]);

        return $response->json();
    }

    public function generateActivities($projectId)
    {
        $response = Http::post("{$this->baseUrl}/generate-activities/{$projectId}");

        return $response->json();
    }

    public function generateTasks($activityId)
    {
        $response = Http::post("{$this->baseUrl}/generate-tasks/{$activityId}");

        return $response->json();
    }

    public function createProjectViaChat($title, $description)
    {
        $response = Http::post("{$this->baseUrl}/chat/create-project", [
            'title' => $title,
            'description' => $description,
        ]);

        return $response->json();
    }

    public function addTaskViaChat($taskTitle, $projectName, $context = "")
    {
        $response = Http::post("{$this->baseUrl}/chat/add-task", [
            'task_title' => $taskTitle,
            'project_name' => $projectName,
            'context' => $context,
        ]);

        return $response->json();
    }

    public function modifyProjectViaChat($projectName, $modification)
    {
        $response = Http::post("{$this->baseUrl}/chat/modify-project", [
            'project_name' => $projectName,
            'modification' => $modification,
        ]);

        return $response->json();
    }

    public function sendChatMessage($message, $context = [])
    {
        $response = Http::post("{$this->baseUrl}/chat/message", [
            'message' => $message,
            'context' => $context,
        ]);

        return $response->json();
    }

    public function getChatHistory($sessionId = null)
    {
        $params = $sessionId ? ['session_id' => $sessionId] : [];
        $response = Http::get("{$this->baseUrl}/chat/history", $params);

        return $response->json();
    }
}
