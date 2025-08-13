@extends('layout')
@section('content')

<style>
  .card {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    margin: 20px auto;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    width: 100%;
    max-width: 820px;
  }
  h2 {
    margin-bottom: 20px;
    font-size: 1.6rem;
    color: #333;
  }
  label {
    display: block;
    font-weight: 500;
    margin-bottom: 6px;
    color: #444;
  }
  input[type="text"],
  input[type="date"],
  textarea,
  select {
    width: 100%;
    padding: 8px 10px;
    border-radius: 4px;
    border: 1px solid #ccc;
    font-size: 1rem;
    box-sizing: border-box;
  }
  textarea {
    min-height: 100px;
    resize: vertical;
  }
  .form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
  }
  .form-row > div {
    flex: 1;
    min-width: 200px;
  }
  .btn {
    padding: 8px 14px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    background: #ccc;
    font-size: 0.95rem;
    transition: background 0.3s;
  }
  .btn.primary {
    background: #007BFF;
    color: white;
  }
  .btn.primary:hover {
    background: #0069d9;
  }
  .btn:hover {
    background: #bbb;
  }
  .panel {
    background: #f9f9f9;
    border-radius: 6px;
    padding: 10px;
    border: 1px solid #ddd;
  }
  .small {
    font-size: 0.9rem;
    color: #555;
  }

  /* Responsive */
  @media (max-width: 600px) {
    .form-row {
      flex-direction: column;
    }
    .btn {
      width: 100%;
    }
  }
</style>

<div class="card">
  <h2>{{ $activity->exists ? 'Modifier l’activité' : 'Nouvelle activité' }}</h2>
  <form id="activity-form" method="POST" action="{{ $activity->exists ? route('activities.update',[$project,$activity]) : route('activities.store',$project) }}">
    @csrf
    @if($activity->exists) @method('PUT') @endif

    <div>
      <label>Titre</label>
      <input type="text" name="title" value="{{ old('title',$activity->title) }}">
    </div>

    <div style="margin-top:10px">
      <label>Description</label>
      <textarea name="description">{{ old('description',$activity->description) }}</textarea>
    </div>

    <div class="form-row" style="margin-top:10px">
      <div>
        <label>Échéance</label>
        <input type="date" name="due_date" value="{{ old('due_date',$activity->due_date) }}">
      </div>
      @if($activity->exists)
      <div>
        <label>Statut</label>
        <select name="status">
          <option value="in_progress" @selected($activity->status==='in_progress')>En cours</option>
          <option value="completed" @selected($activity->status==='completed')>Terminé</option>
        </select>
      </div>
      @endif
    </div>

    <div style="margin-top:12px; display:flex; flex-wrap: wrap; gap:8px; align-items:center;">
      <button class="btn primary">{{ $activity->exists ? 'Enregistrer' : 'Créer' }}</button>
      @if(!$activity->exists)
      <button type="button" class="btn" id="ai-list">IA: proposer une liste de tâches</button>
      @endif
    </div>

    @if(!$activity->exists)
    <div class="panel" style="margin-top:12px">
      <div class="small" style="margin-bottom:8px">Tâches à créer pour cette activité</div>
      <div data-dyn-list></div>
      <button type="button" class="btn" data-add-row="[data-dyn-list]">Ajouter une ligne</button>
    </div>
    @endif
  </form>
</div>

<script>
const actForm = document.getElementById('activity-form');
idleSuggestForForm(actForm, {
  url: '{{ route('ai.suggest.task_fields') }}',
  titleSel:'input[name="title"]',
  descSel:'textarea[name="description"]',
  projectId: {{ $project->id }},
  aiMode:'fields'
});
const aiListBtn = document.getElementById('ai-list');
if (aiListBtn) {
  aiListBtn.addEventListener('click', ()=>{
    const params = new URLSearchParams({
      project_id: '{{ $project->id }}',
      activity_title: actForm.querySelector('input[name="title"]').value,
      activity_description: actForm.querySelector('textarea[name="description"]').value
    });
    fetch('{{ route('ai.suggest.task_list') }}?'+params.toString(), { headers:{'X-Requested-With':'XMLHttpRequest'} })
    .then(r=>r.json()).then(data=>{
      const container = actForm.querySelector('[data-dyn-list]');
      container.innerHTML = '';
      data.tasks.forEach(t=>addTaskRow(container, t.title, t.description, t.priority));
    });
  });
}
</script>

@endsection
