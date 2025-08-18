<?php

namespace App\Http\Controllers;

use App\Services\AIClientService;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    protected $aiService;

    public function __construct(AIClientService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function handleMessage(Request $request)
    {
        $message = trim($request->input('message'));

        // Check if message starts with a command
        if (strpos($message, '/') === 0) {
            return $this->handleCommand($message);
        }

        // Fallback to AI response for non-command messages
        return response()->json([
            'reply' => "Je n'ai pas compris. Utilisez /help pour voir la liste des commandes disponibles."
        ]);
    }

    protected function handleCommand(string $message)
    {
        $parts = explode(' ', $message, 2);
        $command = strtolower($parts[0]);
        $parameters = $parts[1] ?? '';

        switch ($command) {
            case '/help':
                return $this->showHelp();
            case '/create-project':
                return $this->createProjectCommand($parameters);
            case '/list-projects':
                return $this->listProjectsCommand();
            default:
                return response()->json([
                    'reply' => "Commande inconnue : {$command}. Utilisez /help pour voir la liste des commandes disponibles."
                ]);
        }
    }

    protected function showHelp()
    {
        $helpText = "**Commandes disponibles :**\n\n";
        $helpText .= "** Gestion des projets :**\n";
        $helpText .= "• `/create-project [nom du projet]` - Créer un nouveau projet\n";
        $helpText .= "• `/list-projects` - Lister tous les projets\n\n";



        $helpText .= "** Autres :**\n";
        $helpText .= "• `/help` - Afficher cette aide\n\n";

        $helpText .= "**Exemples :**\n";
        $helpText .= "• `/create-project Mon nouveau site web`\n";

        return response()->json([
            'reply' => $helpText
        ]);
    }

    protected function createProjectCommand(string $parameters)
    {
        if (empty(trim($parameters))) {
            return response()->json([
                'reply' => " Usage: /create-project [nom du projet]\nExemple: /create-project Mon nouveau site web"
            ]);
        }

        // Vérifier que l'utilisateur est un manager
        if (!request()->user()->isManager()) {
            return response()->json([
                'reply' => " Accès refusé : Seuls les managers peuvent créer des projets.",
                'error' => 'Insufficient permissions'
            ], 403);
        }

        $title = trim($parameters);

        try {
            // Appeler l'AI service
            $result = $this->aiService->createProjectFromTitle($title);

            // Préparer les données comme dans le ProjectController
            $data = [
                'title' => $result['project']['title'] ?? $title,
                'description' => $result['project']['description'] ?? '',
                'owner_id' => request()->user()->id,
                'status' => 'in_progress',
            ];

            $data['due_date'] = now()->addDays(30)->format('Y-m-d');

            // Sauvegarde en base
            $project = Project::create($data);

            return response()->json([
                'reply' => " Projet créé avec succès : {$project->title}",
                'project' => $project,
                'owner' => request()->user()->name
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création du projet via commande', [
                'title' => $title,
                'user_id' => request()->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'reply' => " Erreur lors de la création du projet. Veuillez réessayer.",
                'error' => 'Creation failed'
            ], 500);
        }
    }



    protected function listProjectsCommand()
    {
        $projects = Project::all();

        if ($projects->isEmpty()) {
            return response()->json([
                'reply' => " Aucun projet trouvé. Utilisez /create-project pour en créer un nouveau."
            ]);
        }

        $list = " **Projets disponibles :**\n";
        foreach ($projects as $project) {
            $list .= "• {$project->title} ({$project->status})\n";
        }

        return response()->json([
            'reply' => $list
        ]);
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

    protected function createProjectFromChat(string $message)
    {
        // Extraire le titre du projet (exemple simplifié)
        preg_match('/projet\s+(.*)$/i', $message, $matches);
        $title = $matches[1] ?? "Projet sans titre";

        // Appeler l'AI service
        $result = $this->aiService->createProjectFromTitle($title);

        // Sauvegarde en base
        $project = Project::create([
            'title' => $result['project']['title'] ?? $title,
            'description' => $result['project']['description'] ?? '',
            'status' => 'active',
        ]);

        return response()->json([
            'reply' => "Projet créé avec succès : {$project->title}",
            'project' => $project
        ]);
    }

}
