@extends('layout')
@section('content')

<div class="page-container">
    <div class="page-header">
        <div class="page-title">
            <h1>{{ $project->title }}</h1>
            <div class="project-meta">
                <span class="badge badge-{{ $project->status }}">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span>
                @if($project->due_date)
                    <span class="due-date">
                        <i class="fas fa-calendar"></i>
                        {{ \Carbon\Carbon::parse($project->due_date)->format('d/m/Y') }}
                    </span>
                @endif
            </div>
        </div>
        @if($user->id === $project->owner_id)
            <div class="page-actions">
                <a class="btn btn-secondary" href="{{ route('projects.edit',$project) }}">
                    <i class="fas fa-edit"></i> Modifier
                </a>
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
            <div class="stat-value">{{ $project->activities->sum(function($a) { return $a->tasks->count(); }) }}</div>
            <div class="stat-label">Tâches totales</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $project->activities->sum(function($a) { return $a->tasks->where('status', 'completed')->count() + $a->tasks->where('status', 'finalized')->count(); }) }}</div>
            <div class="stat-label">Tâches terminées</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">
                @php
                    $totalTasks = $project->activities->sum(function($a) { return $a->tasks->count(); });
                    $completedTasks = $project->activities->sum(function($a) { return $a->tasks->where('status', 'completed')->count() + $a->tasks->where('status', 'finalized')->count(); });
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
                    @if($project->due_date)
                        <div class="info-item">
                            <label>Échéance</label>
                            <div class="due-date-info {{ \Carbon\Carbon::parse($project->due_date)->isPast() ? 'overdue' : '' }}">
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
                @if($user->id === $project->owner_id)
                    <a class="btn btn-primary btn-sm" href="{{ route('activities.create',$project) }}">
                        <i class="fas fa-plus"></i> Ajouter
                    </a>
                @endif
            </div>
            <div class="card-content">
                @if($project->activities->count() > 0)
                    <div class="activities-list">
                        @foreach($project->activities as $activity)
                            <div class="activity-item">
                                <div class="activity-info">
                                    <h4>
                                        <a href="{{ route('activities.show',$activity) }}" class="link">
                                            {{ $activity->title }}
                                        </a>
                                    </h4>
                                    <p class="activity-description">{{ Str::limit($activity->description, 100) ?: 'Aucune description' }}</p>
                                    <div class="activity-meta">
                                        <span class="badge badge-{{ $activity->status }}">{{ ucfirst(str_replace('_', ' ', $activity->status)) }}</span>
                                        <span class="task-count">
                                            <i class="fas fa-tasks"></i>
                                            {{ $activity->tasks->count() }} tâches
                                        </span>
                                        @if($activity->tasks->count() > 0)
                                            @php
                                                $completedTasks = $activity->tasks->where('status', 'completed')->count() + $activity->tasks->where('status', 'finalized')->count();
                                                $activityProgress = round(($completedTasks / $activity->tasks->count()) * 100);
                                            @endphp
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: {{ $activityProgress }}%"></div>
                                                <span class="progress-text">{{ $activityProgress }}%</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="activity-actions">
                                    @if($user->id === $project->owner_id)
                                        <a href="{{ route('activities.edit',[$project,$activity]) }}" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('activities.show',$activity) }}" class="btn btn-sm btn-primary">
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
                        @if($user->id === $project->owner_id)
                            <a class="btn btn-primary" href="{{ route('activities.create',$project) }}">
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
    @if($recentTasks->count() > 0)
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
                            @foreach($recentTasks as $task)
                                <tr>
                                    <td>
                                        <div class="task-info">
                                            <strong>{{ $task->title }}</strong>
                                            @if($task->priority !== 'medium')
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
                                        @if($task->due_date)
                                            <div class="due-date-cell {{ \Carbon\Carbon::parse($task->due_date)->isPast() && $task->status !== 'completed' && $task->status !== 'finalized' ? 'overdue' : '' }}">
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

.grid-2 { grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); }

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

.badge-in_progress { background: #dbeafe; color: #1e40af; }
.badge-completed { background: #d1fae5; color: #065f46; }
.badge-archived { background: var(--gray-200); color: var(--gray-600); }
.badge-pending { background: #fef3c7; color: #92400e; }
.badge-finalized { background: #d1fae5; color: #065f46; }
.badge-completed_by_assignee { background: #fed7aa; color: #9a3412; }
.badge-open { background: #fef3c7; color: #92400e; }

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
}
</style>

<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

@endsection
