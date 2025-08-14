@extends('layout')
@section('content')

    <div class="page-container">
        <div class="page-header">
            <div class="page-title">
                <h1>{{ $project->title }}</h1>
                <div class="project-meta">
                    <span
                        class="badge badge-{{ $project->status }}">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span>
                    @if ($project->due_date)
                        <span class="due-date">
                            <i class="fas fa-calendar"></i>
                            {{ \Carbon\Carbon::parse($project->due_date)->format('d/m/Y') }}
                        </span>
                    @endif
                </div>
            </div>
            @if ($user->id === $project->owner_id)
                <div class="page-actions">
                    <a class="btn btn-secondary" href="{{ route('projects.edit', $project) }}">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                    <button id="analyze-btn" class="btn btn-primary" onclick="analyzeProject({{ $project->id }})">
                        <i class="fas fa-robot"></i>
                        <span id="analyze-btn-text">Analyser avec l'IA</span>
                    </button>
                </div>
            @endif
        </div>

        <!-- Project Stats -->
        <div class="stats-row">
            <div class="stat-item">
                <div class="stat-value">{{ $project->activities->count() }}</div>
                <div class="stat-label">Activités</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $project->activities->sum(function ($a) {return $a->tasks->count();}) }}</div>
                <div class="stat-label">Taches totales</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">
                    {{ $project->activities->sum(function ($a) {return $a->tasks->where('status', 'completed')->count() + $a->tasks->where('status', 'finalized')->count();}) }}
                </div>
                <div class="stat-label">Taches terminées</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">
                    @php
                        $totalTasks = $project->activities->sum(function ($a) {
                            return $a->tasks->count();
                        });
                        $completedTasks = $project->activities->sum(function ($a) {
                            return $a->tasks->where('status', 'completed')->count() +
                                $a->tasks->where('status', 'finalized')->count();
                        });
                        $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
                    @endphp
                    {{ $progress }}%
                </div>
                <div class="stat-label">Progression</div>
            </div>
        </div>

        <div class="grid grid-2">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-info-circle"></i> Détails du projet</h3>
                </div>
                <div class="card-content">
                    <div class="info-section">
                        <div class="info-item">
                            <label>Description</label>
                            <p>{{ $project->description ?: 'Aucune description disponible' }}</p>
                        </div>
                        <div class="info-row">
                            <div class="info-item">
                                <label>Manager</label>
                                <div class="owner-info">
                                    <i class="fas fa-user"></i>
                                    {{ $project->owner->name }}
                                </div>
                            </div>
                            <div class="info-item">
                                <label>Date de création</label>
                                <div class="date-info">
                                    <i class="fas fa-calendar-plus"></i>
                                    {{ $project->created_at->format('d/m/Y') }}
                                </div>
                            </div>
                        </div>
                        @if ($project->due_date)
                            <div class="info-item">
                                <label>Echéance</label>
                                <div
                                    class="due-date-info {{ \Carbon\Carbon::parse($project->due_date)->isPast() ? 'overdue' : '' }}">
                                    <i class="fas fa-flag"></i>
                                    {{ \Carbon\Carbon::parse($project->due_date)->format('d/m/Y') }}
                                    <small>({{ \Carbon\Carbon::parse($project->due_date)->diffForHumans() }})</small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-clipboard-list"></i> Activités ({{ $project->activities->count() }})</h3>
                    @if ($user->id === $project->owner_id)
                        <a class="btn btn-primary btn-sm" href="{{ route('activities.create', $project) }}">
                            <i class="fas fa-plus"></i> Ajouter
                        </a>
                    @endif
                </div>
                <div class="card-content">
                    @if ($project->activities->count() > 0)
                        <div class="activities-list">
                            @foreach ($project->activities as $activity)
                                <div class="activity-item">
                                    <div class="activity-info">
                                        <h4>
                                            <a href="{{ route('activities.show', $activity) }}" class="link">
                                                {{ $activity->title }}
                                            </a>
                                        </h4>
                                        <p class="activity-description">
                                            {{ Str::limit($activity->description, 100) ?: 'Aucune description' }}</p>
                                        <div class="activity-meta">
                                            <span
                                                class="badge badge-{{ $activity->status }}">{{ ucfirst(str_replace('_', ' ', $activity->status)) }}</span>
                                            <span class="task-count">
                                                <i class="fas fa-tasks"></i>
                                                {{ $activity->tasks->count() }} tâches
                                            </span>
                                            @if ($activity->tasks->count() > 0)
                                                @php
                                                    $completedTasks =
                                                        $activity->tasks->where('status', 'completed')->count() +
                                                        $activity->tasks->where('status', 'finalized')->count();
                                                    $activityProgress = round(
                                                        ($completedTasks / $activity->tasks->count()) * 100,
                                                    );
                                                @endphp
                                                <div class="progress-bar">
                                                    <div class="progress-fill" style="width: {{ $activityProgress }}%">
                                                    </div>
                                                    <span class="progress-text">{{ $activityProgress }}%</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="activity-actions">
                                        @if ($user->id === $project->owner_id)
                                            <a href="{{ route('activities.edit', [$project, $activity]) }}"
                                                class="btn btn-sm btn-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('activities.show', $activity) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-clipboard-list"></i>
                            <p>Aucune activité dans ce projet</p>
                            @if ($user->id === $project->owner_id)
                                <a class="btn btn-primary" href="{{ route('activities.create', $project) }}">
                                    <i class="fas fa-plus"></i> Créer la première activité
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Tasks Section -->
        @php
            $recentTasks = $project->activities->flatMap->tasks->sortByDesc('created_at')->take(10);
        @endphp
        @if ($recentTasks->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-tasks"></i> Tâches récentes</h3>
                </div>
                <div class="card-content">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tâche</th>
                                    <th>Activité</th>
                                    <th>Assignés</th>
                                    <th>Statut</th>
                                    <th>Échéance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentTasks as $task)
                                    <tr>
                                        <td>
                                            <div class="task-info">
                                                <strong>{{ $task->title }}</strong>
                                                @if ($task->priority !== 'medium')
                                                    <span class="priority-indicator priority-{{ $task->priority }}">
                                                        {{ $task->priority === 'high' ? '🔴' : '🟡' }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('activities.show', $task->activity) }}" class="link">
                                                {{ $task->activity->title }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="assignees">
                                                @forelse($task->assignees as $assignee)
                                                    <span class="assignee-badge">{{ $assignee->name }}</span>
                                                @empty
                                                    <span class="text-muted">Non assigné</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $task->status }}">
                                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($task->due_date)
                                                <div
                                                    class="due-date-cell {{ \Carbon\Carbon::parse($task->due_date)->isPast() && $task->status !== 'completed' && $task->status !== 'finalized' ? 'overdue' : '' }}">
                                                    <i class="fas fa-calendar"></i>
                                                    {{ \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') }}
                                                </div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div id="ai-modal" class="ai-modal hidden">
        <div class="ai-modal-overlay" onclick="closeAIModal()"></div>
        <div class="ai-modal-content">
            <div id="loading-state" class="text-center py-8">
                <div class="loading-spinner"></div>
                <h3 class="text-xl font-semibold text-gray-800 mt-4 mb-2">Analyse IA en cours...</h3>
                <p class="text-gray-600">L'IA analyse votre projet et génère des activités optimisées</p>
            </div>

            <div id="results-state" class="hidden">
                <div class="ai-modal-header">
                    <h2 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-robot text-primary"></i>
                        Activités générées par l'IA
                    </h2>
                    <button onclick="closeAIModal()" class="close-btn">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="ai-modal-body">
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-blue-800 text-sm">
                            <i class="fas fa-info-circle mr-2"></i>
                            Voici les activités et tâches suggérées par l'IA. Vous pouvez les modifier avant de les valider.
                        </p>
                    </div>

                    <div id="generated-activities" class="space-y-6">

                    </div>
                </div>

                <div class="ai-modal-footer">
                    <button onclick="closeAIModal()" class="btn btn-secondary">
                        <i class="fas fa-times mr-2"></i>
                        Annuler
                    </button>
                    <button onclick="validateActivities()" class="btn btn-primary">
                        <i class="fas fa-check mr-2"></i>
                        <span id="validate-btn-text">Valider et créer</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Variables héritées du dashboard */
        :root {
            --primary-color: #3b82f6;
            --secondary-color: #6b7280;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06b6d4;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --border-radius: 8px;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --transition: all 0.2s ease-in-out;
        }

        /* Page Layout */
        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            gap: 20px;
        }

        .page-title h1 {
            margin: 0 0 8px 0;
            color: var(--gray-800);
            font-size: 28px;
            font-weight: 700;
        }

        .project-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .due-date {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 14px;
            color: var(--gray-600);
        }

        .page-actions {
            display: flex;
            gap: 12px;
        }

        /* Stats Row */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow-sm);
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 12px;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Grid System */
        .grid {
            display: grid;
            gap: 24px;
            margin-bottom: 24px;
        }

        .grid-2 {
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        }

        /* Card Component */
        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: var(--transition);
        }

        .card:hover {
            box-shadow: var(--shadow-md);
        }

        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--gray-200);
            background: var(--gray-100);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h3 {
            margin: 0;
            color: var(--gray-800);
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-content {
            padding: 20px;
        }

        /* Info Section */
        .info-section {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .info-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .info-item label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .info-item p {
            margin: 0;
            color: var(--gray-800);
            line-height: 1.5;
        }

        .owner-info,
        .date-info {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--gray-700);
        }

        .due-date-info {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--gray-700);
        }

        .due-date-info.overdue {
            color: var(--danger-color);
        }

        .due-date-info small {
            color: var(--gray-500);
            margin-left: 4px;
        }

        /* Activities List */
        .activities-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .activity-item {
            border: 1px solid var(--gray-200);
            border-radius: var(--border-radius);
            padding: 16px;
            transition: var(--transition);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
        }

        .activity-item:hover {
            box-shadow: var(--shadow-sm);
            border-color: var(--gray-300);
        }

        .activity-info {
            flex: 1;
        }

        .activity-info h4 {
            margin: 0 0 8px 0;
            font-size: 16px;
        }

        .activity-description {
            margin: 0 0 8px 0;
            color: var(--gray-600);
            font-size: 14px;
            line-height: 1.4;
        }

        .activity-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .task-count {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            color: var(--gray-600);
        }

        .progress-bar {
            position: relative;
            width: 80px;
            height: 16px;
            background: var(--gray-200);
            border-radius: 8px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--success-color);
            transition: width 0.3s ease;
        }

        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 10px;
            font-weight: 600;
            color: var(--gray-700);
        }

        .activity-actions {
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }

        /* Table Component */
        .table-container {
            overflow-x: auto;
            margin: -20px;
            padding: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        .table th,
        .table td {
            text-align: left;
            padding: 12px 8px;
            border-bottom: 1px solid var(--gray-200);
        }

        .table th {
            font-weight: 600;
            color: var(--gray-700);
            background: var(--gray-100);
            font-size: 14px;
        }

        .table td {
            font-size: 14px;
        }

        .task-info {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .priority-indicator {
            font-size: 12px;
        }

        .assignees {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }

        .assignee-badge {
            background: var(--primary-color);
            color: white;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .due-date-cell {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
        }

        .due-date-cell.overdue {
            color: var(--danger-color);
            font-weight: 600;
        }

        /* Button Component */
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            gap: 6px;
            white-space: nowrap;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }

        .btn-primary:disabled {
            background: var(--gray-400);
            cursor: not-allowed;
            transform: none;
        }

        .btn-secondary {
            background: var(--gray-200);
            color: var(--gray-700);
        }

        .btn-secondary:hover {
            background: var(--gray-300);
        }

        .btn-sm {
            padding: 6px 10px;
            font-size: 12px;
        }

        /* Badge Component */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .badge-in_progress {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-archived {
            background: var(--gray-200);
            color: var(--gray-600);
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-finalized {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-completed_by_assignee {
            background: #fed7aa;
            color: #9a3412;
        }

        .badge-open {
            background: #fef3c7;
            color: #92400e;
        }

        /* Link Component */
        .link {
            color: var(--primary-color);
            text-decoration: none;
            transition: var(--transition);
        }

        .link:hover {
            color: #2563eb;
            text-decoration: underline;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--gray-500);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            color: var(--gray-400);
        }

        .empty-state p {
            margin: 0 0 16px 0;
            font-size: 16px;
        }

        /* AI Modal Styles */
        .ai-modal {
            position: fixed;
            inset: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .ai-modal.hidden {
            display: none;
        }

        .ai-modal-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .ai-modal-content {
            position: relative;
            background: white;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .ai-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px 24px 0 24px;
            margin-bottom: 20px;
        }

        .ai-modal-body {
            padding: 0 24px;
            max-height: 60vh;
            overflow-y: auto;
        }

        .ai-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 20px 24px 24px 24px;
            border-top: 1px solid var(--gray-200);
            margin-top: 20px;
        }

        .close-btn {
            background: var(--gray-100);
            border: none;
            border-radius: 8px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .close-btn:hover {
            background: var(--gray-200);
        }

        .loading-spinner {
            width: 48px;
            height: 48px;
            border: 4px solid var(--gray-200);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Generated Activity Styles */
        .generated-activity {
            border: 1px solid var(--gray-200);
            border-radius: var(--border-radius);
            padding: 20px;
            background: #fafafa;
        }

        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
            gap: 16px;
        }

        .activity-header input {
            font-size: 18px;
            font-weight: 600;
            border: 1px solid var(--gray-300);
            border-radius: 6px;
            padding: 8px 12px;
            flex: 1;
        }

        .activity-header textarea {
            border: 1px solid var(--gray-300);
            border-radius: 6px;
            padding: 8px 12px;
            resize: vertical;
            min-height: 60px;
            width: 100%;
            margin-top: 8px;
        }

        .remove-activity-btn {
            background: var(--danger-color);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 6px 10px;
            cursor: pointer;
            font-size: 12px;
            transition: var(--transition);
            flex-shrink: 0;
        }

        .remove-activity-btn:hover {
            background: #dc2626;
        }

        .tasks-list {
            margin-top: 16px;
        }

        .task-item {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: 6px;
            padding: 16px;
            margin-bottom: 12px;
            position: relative;
        }

        .task-item:last-child {
            margin-bottom: 0;
        }

        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
        }

        .task-header input {
            font-weight: 500;
            border: 1px solid var(--gray-300);
            border-radius: 4px;
            padding: 6px 10px;
            flex: 1;
            font-size: 14px;
        }

        .remove-task-btn {
            background: var(--danger-color);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 4px 8px;
            cursor: pointer;
            font-size: 11px;
            flex-shrink: 0;
        }

        .task-details {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 12px;
            align-items: flex-start;
        }

        .task-details textarea {
            border: 1px solid var(--gray-300);
            border-radius: 4px;
            padding: 6px 10px;
            resize: vertical;
            min-height: 40px;
            font-size: 13px;
        }

        .task-details select,
        .task-details input[type="number"] {
            border: 1px solid var(--gray-300);
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 13px;
        }

        .add-task-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            cursor: pointer;
            font-size: 13px;
            margin-top: 12px;
            transition: var(--transition);
        }

        .add-task-btn:hover {
            background: #2563eb;
        }

        /* Utility Classes */
        .text-muted {
            color: var(--gray-500);
            font-size: 12px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .page-container {
                padding: 16px;
            }

            .page-header {
                flex-direction: column;
                align-items: stretch;
            }

            .page-actions {
                justify-content: flex-end;
            }

            .grid-2 {
                grid-template-columns: 1fr;
            }

            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }

            .info-row {
                grid-template-columns: 1fr;
            }

            .activity-item {
                flex-direction: column;
                align-items: stretch;
            }

            .activity-actions {
                justify-content: flex-end;
            }

            .table-container {
                margin: -16px;
                padding: 16px;
            }

            .project-meta {
                flex-direction: column;
                align-items: flex-start;
            }

            .ai-modal-content {
                margin: 10px;
                max-height: 95vh;
            }

            .ai-modal-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .task-details {
                grid-template-columns: 1fr;
            }

            .activity-header {
                flex-direction: column;
                align-items: stretch;
            }
        }

        @media (max-width: 480px) {
            .page-container {
                padding: 12px;
            }

            .page-title h1 {
                font-size: 24px;
            }

            .card-content {
                padding: 16px;
            }

            .stats-row {
                grid-template-columns: 1fr;
                padding: 16px;
            }

            .activity-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .ai-modal {
                padding: 10px;
            }
        }
    </style>

    <script>
        let currentProjectId = {{ $project->id }};
        let generatedActivities = [];

        function analyzeProject(projectId) {
            const analyzeBtn = document.getElementById('analyze-btn');
            const analyzeBtnText = document.getElementById('analyze-btn-text');
            const modal = document.getElementById('ai-modal');
            const loadingState = document.getElementById('loading-state');
            const resultsState = document.getElementById('results-state');

            analyzeBtn.disabled = true;
            analyzeBtnText.textContent = 'Analyse en cours...';

            modal.classList.remove('hidden');
            loadingState.classList.remove('hidden');
            resultsState.classList.add('hidden');

            fetch(`/api/ai/projects/${projectId}/analyze`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                // Vérifier le content-type
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Response is not JSON');
                }

                return response.json();
            })
            .then(data => {
                console.log('Received data:', data);

                analyzeBtn.disabled = false;
                analyzeBtnText.textContent = 'Analyser avec l\'IA';

                if (data.success) {
                    generatedActivities = data.analysis.activities || [];

                    displayGeneratedActivities(generatedActivities);

                    loadingState.classList.add('hidden');
                    resultsState.classList.remove('hidden');
                } else {
                    closeAIModal();
                    showNotification('Erreur lors de l\'analyse: ' + (data.message || 'Erreur inconnue'), 'error');
                }
            })
            .catch(error => {
                console.error('Erreur complète:', error);
                analyzeBtn.disabled = false;
                analyzeBtnText.textContent = 'Analyser avec l\'IA';
                closeAIModal();

                let errorMessage = 'Erreur lors de l\'analyse du projet';
                if (error.message.includes('404')) {
                    errorMessage = 'Route non trouvée. Vérifiez la configuration des routes API.';
                } else if (error.message.includes('500')) {
                    errorMessage = 'Erreur serveur. Vérifiez les logs du serveur.';
                } else if (error.message.includes('JSON')) {
                    errorMessage = 'Réponse serveur invalide. Vérifiez que l\'API retourne du JSON.';
                }

                showNotification(errorMessage, 'error');
            });
        }

        function displayGeneratedActivities(activities) {
            const container = document.getElementById('generated-activities');
            container.innerHTML = '';

            activities.forEach((activity, activityIndex) => {
                const activityHtml = `
                    <div class="generated-activity" data-activity-index="${activityIndex}">
                        <div class="activity-header">
                            <div style="flex: 1;">
                                <input
                                    type="text"
                                    value="${escapeHtml(activity.title)}"
                                    placeholder="Titre de l'activité"
                                    onchange="updateActivityTitle(${activityIndex}, this.value)"
                                />
                                <textarea
                                    placeholder="Description de l'activité"
                                    onchange="updateActivityDescription(${activityIndex}, this.value)"
                                >${escapeHtml(activity.description || '')}</textarea>
                            </div>
                            <button
                                class="remove-activity-btn"
                                onclick="removeActivity(${activityIndex})"
                                title="Supprimer cette activité"
                            >
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>

                        <div class="tasks-list">
                            <h4 style="margin: 0 0 12px 0; font-size: 16px; color: var(--gray-700);">
                                <i class="fas fa-tasks mr-2"></i>
                                Tâches (${activity.tasks ? activity.tasks.length : 0})
                            </h4>
                            <div class="tasks-container" id="tasks-${activityIndex}">
                                ${activity.tasks ? activity.tasks.map((task, taskIndex) => generateTaskHtml(task, activityIndex, taskIndex)).join('') : ''}
                            </div>
                            <button
                                class="add-task-btn"
                                onclick="addNewTask(${activityIndex})"
                            >
                                <i class="fas fa-plus mr-2"></i>
                                Ajouter une tâche
                            </button>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', activityHtml);
            });
        }

        function generateTaskHtml(task, activityIndex, taskIndex) {
            return `
                <div class="task-item" data-task-index="${taskIndex}">
                    <div class="task-header">
                        <input
                            type="text"
                            value="${escapeHtml(task.title)}"
                            placeholder="Titre de la tâche"
                            onchange="updateTaskTitle(${activityIndex}, ${taskIndex}, this.value)"
                        />
                        <button
                            class="remove-task-btn"
                            onclick="removeTask(${activityIndex}, ${taskIndex})"
                            title="Supprimer cette tâche"
                        >
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="task-details">
                        <textarea
                            placeholder="Description de la tâche"
                            onchange="updateTaskDescription(${activityIndex}, ${taskIndex}, this.value)"
                        >${escapeHtml(task.description || '')}</textarea>
                        <select onchange="updateTaskPriority(${activityIndex}, ${taskIndex}, this.value)">
                            <option value="low" ${task.priority === 'low' ? 'selected' : ''}>Faible</option>
                            <option value="medium" ${task.priority === 'medium' ? 'selected' : ''}>Moyenne</option>
                            <option value="high" ${task.priority === 'high' ? 'selected' : ''}>Élevée</option>
                        </select>
                        <input
                            type="number"
                            placeholder="Heures"
                            value="${task.estimated_hours || ''}"
                            min="1"
                            style="width: 80px;"
                            onchange="updateTaskHours(${activityIndex}, ${taskIndex}, this.value)"
                        />
                    </div>
                </div>
            `;
        }

        function updateActivityTitle(activityIndex, value) {
            if (generatedActivities[activityIndex]) {
                generatedActivities[activityIndex].title = value;
            }
        }

        function updateActivityDescription(activityIndex, value) {
            if (generatedActivities[activityIndex]) {
                generatedActivities[activityIndex].description = value;
            }
        }

        function updateTaskTitle(activityIndex, taskIndex, value) {
            if (generatedActivities[activityIndex] && generatedActivities[activityIndex].tasks[taskIndex]) {
                generatedActivities[activityIndex].tasks[taskIndex].title = value;
            }
        }

        function updateTaskDescription(activityIndex, taskIndex, value) {
            if (generatedActivities[activityIndex] && generatedActivities[activityIndex].tasks[taskIndex]) {
                generatedActivities[activityIndex].tasks[taskIndex].description = value;
            }
        }

        function updateTaskPriority(activityIndex, taskIndex, value) {
            if (generatedActivities[activityIndex] && generatedActivities[activityIndex].tasks[taskIndex]) {
                generatedActivities[activityIndex].tasks[taskIndex].priority = value;
            }
        }

        function updateTaskHours(activityIndex, taskIndex, value) {
            if (generatedActivities[activityIndex] && generatedActivities[activityIndex].tasks[taskIndex]) {
                generatedActivities[activityIndex].tasks[taskIndex].estimated_hours = value ? parseInt(value) : null;
            }
        }

        function addNewTask(activityIndex) {
            if (generatedActivities[activityIndex]) {
                const newTask = {
                    title: '',
                    description: '',
                    priority: 'medium',
                    estimated_hours: null
                };

                generatedActivities[activityIndex].tasks = generatedActivities[activityIndex].tasks || [];
                generatedActivities[activityIndex].tasks.push(newTask);

                const taskIndex = generatedActivities[activityIndex].tasks.length - 1;
                const tasksContainer = document.getElementById(`tasks-${activityIndex}`);
                tasksContainer.insertAdjacentHTML('beforeend', generateTaskHtml(newTask, activityIndex, taskIndex));
            }
        }

        function removeTask(activityIndex, taskIndex) {
            if (generatedActivities[activityIndex] && generatedActivities[activityIndex].tasks[taskIndex]) {
                generatedActivities[activityIndex].tasks.splice(taskIndex, 1);
                displayGeneratedActivities(generatedActivities);
            }
        }

        function removeActivity(activityIndex) {
            generatedActivities.splice(activityIndex, 1);
            displayGeneratedActivities(generatedActivities);
        }

        function validateActivities() {
            const validateBtn = document.querySelector('#results-state .btn-primary');
            const validateBtnText = document.getElementById('validate-btn-text');

            // Vérifier qu'il y a au moins une activité
            if (generatedActivities.length === 0) {
                showNotification('Vous devez avoir au moins une activité', 'error');
                return;
            }

            // Vérifier que chaque activité a un titre et au moins une tâche
            for (let i = 0; i < generatedActivities.length; i++) {
                const activity = generatedActivities[i];
                if (!activity.title.trim()) {
                    showNotification(`L'activité ${i + 1} doit avoir un titre`, 'error');
                    return;
                }
                if (!activity.tasks || activity.tasks.length === 0) {
                    showNotification(`L'activité "${activity.title}" doit avoir au moins une tâche`, 'error');
                    return;
                }

                // Vérifier que chaque tâche a un titre
                for (let j = 0; j < activity.tasks.length; j++) {
                    const task = activity.tasks[j];
                    if (!task.title.trim()) {
                        showNotification(`La tâche ${j + 1} de l'activité "${activity.title}" doit avoir un titre`, 'error');
                        return;
                    }
                }
            }

            // Désactiver le bouton et changer le texte
            validateBtn.disabled = true;
            validateBtnText.textContent = 'Création en cours...';

            // Envoyer les données au serveur avec le bon préfixe
            fetch(`/api/ai/projects/${currentProjectId}/create-activities`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    activities: generatedActivities
                })
            })
            .then(response => {
                console.log('Create activities response status:', response.status);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Response is not JSON');
                }

                return response.json();
            })
            .then(data => {
                console.log('Create activities data:', data);

                validateBtn.disabled = false;
                validateBtnText.textContent = 'Valider et créer';

                if (data.success) {
                    closeAIModal();
                    showNotification(`${data.activities_count} activités créées avec succès!`, 'success');

                    // Recharger la page pour afficher les nouvelles activités
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showNotification('Erreur lors de la création: ' + (data.message || 'Erreur inconnue'), 'error');
                }
            })
            .catch(error => {
                console.error('Erreur lors de la création:', error);
                validateBtn.disabled = false;
                validateBtnText.textContent = 'Valider et créer';

                let errorMessage = 'Erreur lors de la création des activités';
                if (error.message.includes('404')) {
                    errorMessage = 'Route de création non trouvée. Vérifiez la configuration des routes API.';
                } else if (error.message.includes('500')) {
                    errorMessage = 'Erreur serveur lors de la création. Vérifiez les logs du serveur.';
                } else if (error.message.includes('JSON')) {
                    errorMessage = 'Réponse serveur invalide lors de la création.';
                }

                showNotification(errorMessage, 'error');
            });
        }

        function closeAIModal() {
            const modal = document.getElementById('ai-modal');
            modal.classList.add('hidden');

            // Reset des états
            const loadingState = document.getElementById('loading-state');
            const resultsState = document.getElementById('results-state');
            loadingState.classList.remove('hidden');
            resultsState.classList.add('hidden');

            // Reset des variables
            generatedActivities = [];
        }

        function showNotification(message, type = 'info') {
            // Créer l'élément de notification
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                    <span>${message}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="notification-close">
                    <i class="fas fa-times"></i>
                </button>
            `;

            // Ajouter les styles si ils n'existent pas
            if (!document.querySelector('.notification-styles')) {
                const styles = document.createElement('style');
                styles.className = 'notification-styles';
                styles.textContent = `
                    .notification {
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        z-index: 1100;
                        max-width: 400px;
                        padding: 16px;
                        border-radius: 8px;
                        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        animation: slideIn 0.3s ease-out;
                    }

                    .notification-success {
                        background: #d1fae5;
                        border: 1px solid #10b981;
                        color: #065f46;
                    }

                    .notification-error {
                        background: #fee2e2;
                        border: 1px solid #ef4444;
                        color: #991b1b;
                    }

                    .notification-info {
                        background: #dbeafe;
                        border: 1px solid #3b82f6;
                        color: #1e40af;
                    }

                    .notification-content {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        flex: 1;
                    }

                    .notification-close {
                        background: none;
                        border: none;
                        cursor: pointer;
                        padding: 4px;
                        margin-left: 12px;
                        opacity: 0.7;
                        transition: opacity 0.2s;
                    }

                    .notification-close:hover {
                        opacity: 1;
                    }

                    @keyframes slideIn {
                        from {
                            transform: translateX(100%);
                            opacity: 0;
                        }
                        to {
                            transform: translateX(0);
                            opacity: 1;
                        }
                    }
                `;
                document.head.appendChild(styles);
            }

            // Ajouter la notification au DOM
            document.body.appendChild(notification);

            // Supprimer automatiquement après 5 secondes
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Support du mode sombre
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        }
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
            if (event.matches) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        });
    </script>

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

@endsection
