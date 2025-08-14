<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Project;
use App\Models\Activity;
use App\Models\Task;
use Exception;

class AIClientService
{
    protected $pythonApiUrl;

    public function __construct()
    {
        $this->pythonApiUrl = config('app.python_ai_url', 'http://localhost:5000/api/ai');
    }

    /**
     * Analyser un projet et générer des activités avec des tâches
     */
    public function analyzeProject(string $title, string $description): array
    {
        try {
            $response = Http::timeout(30)->post("{$this->pythonApiUrl}/analyze-project", [
                'title' => $title,
                'description' => $description
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('Erreur lors de l\'analyse du projet: ' . $response->body());

        } catch (Exception $e) {
            Log::error('Erreur AIClientService::analyzeProject', [
                'error' => $e->getMessage(),
                'title' => $title
            ]);

            // Retourner une structure par défaut en cas d'erreur
            return $this->getDefaultProjectStructure($title, $description);
        }
    }

    /**
     * Créer les activités et tâches en base de données
     */
    public function createActivitiesAndTasks(Project $project, array $activitiesData): array
    {
        $createdActivities = [];

        try {
            foreach ($activitiesData as $activityData) {
                // Créer l'activité
                $activity = Activity::create([
                    'title' => $activityData['title'],
                    'description' => $activityData['description'] ?? '',
                    'project_id' => $project->id,
                    'status' => 'in_progress',
                    'due_date' => $project->due_date // Même échéance que le projet
                ]);

                $createdTasks = [];

                // Créer les tâches pour cette activité
                if (isset($activityData['tasks']) && is_array($activityData['tasks'])) {
                    foreach ($activityData['tasks'] as $taskData) {
                        $task = Task::create([
                            'title' => $taskData['title'],
                            'description' => $taskData['description'] ?? '',
                            'activity_id' => $activity->id,
                            'priority' => $this->mapPriority($taskData['priority'] ?? 'medium'),
                            'status' => 'pending',
                            'estimated_hours' => $taskData['estimated_hours'] ?? null,
                            'due_date' => $project->due_date
                        ]);

                        $createdTasks[] = $task;
                    }
                }

                $activity->load('tasks');
                $createdActivities[] = $activity;
            }

            return [
                'success' => true,
                'activities' => $createdActivities,
                'message' => 'Activités et tâches créées avec succès'
            ];

        } catch (Exception $e) {
            Log::error('Erreur lors de la création des activités/tâches', [
                'error' => $e->getMessage(),
                'project_id' => $project->id
            ]);

            return [
                'success' => false,
                'error' => 'Erreur lors de la création: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Mapper les priorités
     */
    private function mapPriority(string $priority): string
    {
        $priorityMap = [
            'high' => 'high',
            'medium' => 'medium',
            'low' => 'low',
            'élevée' => 'high',
            'moyenne' => 'medium',
            'faible' => 'low'
        ];

        return $priorityMap[strtolower($priority)] ?? 'medium';
    }

    /**
     * Structure par défaut en cas d'erreur API
     */
    private function getDefaultProjectStructure(string $title, string $description): array
    {
        return [
            'success' => true,
            'analysis' => [
                'activities' => [
                    [
                        'title' => 'Planification et Analyse',
                        'description' => 'Phase initiale de planification et d\'analyse du projet',
                        'tasks' => [
                            [
                                'title' => 'Définir les objectifs',
                                'description' => 'Clarifier et documenter les objectifs du projet',
                                'priority' => 'high',
                                'estimated_hours' => 4
                            ],
                            [
                                'title' => 'Analyser les besoins',
                                'description' => 'Identifier et analyser les besoins détaillés',
                                'priority' => 'high',
                                'estimated_hours' => 8
                            ]
                        ]
                    ],
                    [
                        'title' => 'Développement/Réalisation',
                        'description' => 'Phase principale de réalisation du projet',
                        'tasks' => [
                            [
                                'title' => 'Développer les fonctionnalités principales',
                                'description' => 'Implémenter les fonctionnalités core du projet',
                                'priority' => 'high',
                                'estimated_hours' => 20
                            ],
                            [
                                'title' => 'Tests et validation',
                                'description' => 'Effectuer les tests et valider les résultats',
                                'priority' => 'medium',
                                'estimated_hours' => 12
                            ]
                        ]
                    ],
                    [
                        'title' => 'Finalisation et Livraison',
                        'description' => 'Phase finale de finalisation et livraison',
                        'tasks' => [
                            [
                                'title' => 'Documentation finale',
                                'description' => 'Rédiger la documentation finale du projet',
                                'priority' => 'medium',
                                'estimated_hours' => 6
                            ],
                            [
                                'title' => 'Livraison et déploiement',
                                'description' => 'Livrer le projet final',
                                'priority' => 'high',
                                'estimated_hours' => 4
                            ]
                        ]
                    ]
                ]
            ],
            'project' => [
                'title' => $title,
                'description' => $description
            ]
        ];
    }
}
