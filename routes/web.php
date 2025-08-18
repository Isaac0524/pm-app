<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\MyWorkController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ChatController;

Route::get('/dashboard', fn() => redirect()->route('dashboard'))->name('home');
// Public
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class,'showLogin'])->name('login');
    Route::post('/login', [AuthController::class,'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class,'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class,'index'])->name('dashboard');

    // Page "Mon travail" pour les membres
    Route::get('/my/work', [MyWorkController::class,'index'])->name('my.work');

    Route::resource('projects', ProjectController::class)->except(['destroy']);
    Route::get('/projects/{project}/activities/create', [ActivityController::class,'create'])->name('activities.create');
    Route::post('/projects/{project}/activities', [ActivityController::class,'store'])->name('activities.store');
    Route::get('/projects/{project}/activities/{activity}/edit', [ActivityController::class,'edit'])->name('activities.edit');
    Route::put('/projects/{project}/activities/{activity}', [ActivityController::class,'update'])->name('activities.update');

    Route::get('/activities/{activity}', [ActivityController::class,'show'])->name('activities.show');

    Route::get('/activities/{activity}/tasks/create', [TaskController::class,'create'])->name('tasks.create');
    Route::post('/activities/{activity}/tasks', [TaskController::class,'store'])->name('tasks.store');
    Route::get('/activities/{activity}/tasks/{task}/edit', [TaskController::class,'edit'])->name('tasks.edit');
    Route::put('/activities/{activity}/tasks/{task}', [TaskController::class,'update'])->name('tasks.update');

    // Détail d'une tâche (permet de gérer les sous-tâches pour les membres assignés)
    Route::get('/tasks/{task}', [TaskController::class,'show'])->name('tasks.show');

    // Actions de statut sur les tâches
    Route::post('/tasks/{task}/toggle-progress', [TaskController::class,'toggleProgress'])->name('tasks.toggle_progress');
    Route::post('/tasks/{task}/mark-complete', [TaskController::class,'markCompleteByAssignee'])->name('tasks.complete_by_assignee');
    Route::post('/tasks/{task}/finalize', [TaskController::class,'finalize'])->name('tasks.finalize')->middleware('manager.only');

    // Sous-tâches (créées par les membres assignés à la tâche)
    Route::post('/tasks/{task}/subtasks', [SubtaskController::class,'store'])->name('subtasks.store');
    Route::put('/subtasks/{subtask}', [SubtaskController::class,'update'])->name('subtasks.update');
    Route::post('/subtasks/{subtask}/toggle', [SubtaskController::class,'toggle'])->name('subtasks.toggle');
    Route::delete('/subtasks/{subtask}', [SubtaskController::class,'destroy'])->name('subtasks.destroy');

    // Équipes (manager)
    Route::get('/teams', [TeamController::class,'index'])->name('teams.index')->middleware('manager.only');
    Route::post('/teams', [TeamController::class,'store'])->name('teams.store')->middleware('manager.only');
    Route::post('/teams/{team}/attach', [TeamController::class,'attachUser'])->name('teams.attach')->middleware('manager.only');
    Route::post('/teams/{team}/detach', [TeamController::class,'detachUser'])->name('teams.detach')->middleware('manager.only');

    // Gestion des utilisateurs (manager)
    Route::middleware('manager.only')->group(function () {
        Route::get('/users', [UsersController::class,'index'])->name('users.index');
        Route::post('/users', [UsersController::class,'store'])->name('users.store');
        Route::put('/users/{user}', [UsersController::class, 'update'])->name('users.update');
        Route::put('/users/{user}/reset-password', [UsersController::class,'resetPassword'])->name('users.reset_password');
        Route::put('/users/{user}/role', [UsersController::class,'changeRole'])->name('users.change_role');
        Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');
    });

    // IA - Endpoints AI
    Route::prefix('api/ai')->group(function () {
        // Analyse de projet
        Route::post('/projects/{project}/analyze', [ProjectController::class, 'analyzeProject'])->name('projects.analyze');


        // Génération d'activités
        Route::post('/projects/{project}/create-activities', [ProjectController::class, 'createActivitiesFromAnalysis'])->name('projects.create-activities');


        // Génération de tâches
        Route::post('/generate-tasks/{activity}', [AIController::class, 'generateTasks'])
            ->name('ai.generate.tasks');

        // Chat IA
        Route::post('/chat/add-task', [AIController::class, 'chatAddTask'])
            ->name('ai.chat.add_task');

        // Chat endpoints
        Route::post('/chat/message', [ChatController::class, 'sendMessage'])
            ->name('ai.chat.message');
        Route::post('/chat/handle-message', [ChatController::class, 'handleMessage'])
            ->name('ai.chat.handle_message');
        Route::post('/chat/create-project', [ChatController::class, 'createProject'])
            ->name('ai.chat.create_project');
        Route::post('/chat/add-task', [ChatController::class, 'addTask'])
            ->name('ai.chat.add_task');
        Route::post('/chat/modify-project', [ChatController::class, 'modifyProject'])
            ->name('ai.chat.modify_project');
    });

    // IA - Anciennes routes (conservées pour compatibilité)
    Route::get('/ai/suggest/task-fields', [AIController::class,'suggestTaskFields'])->name('ai.suggest.task_fields')->middleware('manager.only');
    Route::get('/ai/suggest/task-list', [AIController::class,'suggestTaskList'])->name('ai.suggest.task_list')->middleware('manager.only');

    // Daily Reports - Rapports Journaliers
    Route::prefix('daily-reports')->group(function () {
        // User-specific routes
        Route::get('/my-day', [DailyReportController::class, 'myDay'])->name('daily_reports.my_day');
        Route::get('/create', [DailyReportController::class, 'create'])->name('daily_reports.create');
        Route::post('/', [DailyReportController::class, 'store'])->name('daily_reports.store');
        Route::get('/{report}', [DailyReportController::class, 'show'])->name('daily_reports.show');
        Route::get('/{report}/edit', [DailyReportController::class, 'edit'])->name('daily_reports.edit');
        Route::put('/{report}', [DailyReportController::class, 'update'])->name('daily_reports.update');
        Route::delete('/{report}', [DailyReportController::class, 'destroy'])->name('daily_reports.destroy');
        Route::get('/{report}/download', [DailyReportController::class, 'download'])->name('daily_reports.download');

        // Manager routes
        Route::get('/', [DailyReportController::class, 'dailyReports'])->name('daily_reports.daily_reports');
    });
});
