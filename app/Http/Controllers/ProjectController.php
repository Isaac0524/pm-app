<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $projects = $user->isManager()
            ? Project::where('owner_id',$user->id)->latest()->paginate(10)
            : Project::whereHas('activities.tasks.assignees', fn($q)=>$q->where('users.id',$user->id))->latest()->paginate(10);
        return view('projects.index', compact('projects','user'));
    }

    public function create(Request $request)
    {
        if (!$request->user()->isManager()) abort(403);
        return view('projects.create');
    }

    public function store(Request $request)
    {
        if (!$request->user()->isManager()) abort(403);
        $data = $request->validate([
            'title'=>'required|string|max:255',
            'description'=>'nullable|string',
            'due_date'=>'required|date|after:today'
        ]);
        $data['owner_id'] = $request->user()->id;
        $data['status'] = 'in_progress'; // Force le statut à "en cours"
        $project = Project::create($data);
        return redirect()->route('projects.show',$project)->with('ok','Projet créé');
    }

    public function show(Project $project, Request $request)
    {
        $project->load('activities.tasks.assignees');
        $user = $request->user();
        if (!$user->isManager() && $project->owner_id !== $user->id) {
            $allowed = $project->activities()->whereHas('tasks.assignees', fn($q)=>$q->where('users.id',$user->id))->exists();
            if (!$allowed) abort(403);
        }
        return view('projects.show', compact('project','user'));
    }

    public function edit(Project $project, Request $request)
    {
        if ($project->owner_id !== $request->user()->id) abort(403);
        return view('projects.edit', compact('project'));
    }

    private function allActivitiesCompleted(Project $project): bool
    {
        $activities = $project->activities;
        if ($activities->isEmpty()) {
            return false;
        }

        return $activities->every(function ($activity) {
            return $activity->status === 'completed';
        });
    }

    /**
     * Vérifie et remet le projet à "en cours" si au moins une activité est en cours
     * même si le projet est marqué comme terminé
     */
    public function checkAndReopenProject(Project $project): void
    {
        $activities = $project->activities;

        if ($activities->isEmpty()) {
            return;
        }

        // Compter les activités en cours
        $inProgressActivities = $activities->filter(function ($activity) {
            return $activity->status === 'in_progress';
        })->count();

        // Si au moins une activité est en cours, remettre le projet à "en cours"
        if ($inProgressActivities > 0 && $project->status !== 'in_progress') {
            $project->status = 'in_progress';
            $project->save();
        }
    }

    public function update(Project $project, Request $request)
    {
        if ($project->owner_id !== $request->user()->id) abort(403);

        $data = $request->validate([
            'title'=>'required|string|max:255',
            'description'=>'nullable|string',
            'due_date'=>'required|date|after:today',
            'status'=>'required|in:in_progress,archived'
        ]);

        // Le statut "completed" est maintenant géré automatiquement
        // Empêcher l'archivage si le projet n'est pas terminé
        if ($request->status === 'archived' && $project->status !== 'completed') {
            return back()->withErrors(['status' => 'Un projet ne peut être archivé que s\'il est d\'abord marqué comme terminé.'])->withInput();
        }

        $project->update($data);
        return redirect()->route('projects.show',$project)->with('ok','Projet mis à jour');
    }
}
