@extends('layout')
@section('content')
    <div style="display: grid; gap: 1.5rem; padding: 1.5rem; max-width: 1200px; margin: 0 auto; grid-template-columns: 1fr; @media (min-width: 768px) { grid-template-columns: repeat(2, 1fr); }">
        <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
            <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">Équipes</h2>
            <form method="POST" action="{{ route('teams.store') }}" style="margin-bottom: 12px;">
                @csrf
                <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
                    <div style="flex: 1; min-width: 200px; @media (max-width: 600px) { min-width: 100%; }">
                        <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Nom</label>
                        <input type="text" name="name" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                    </div>
                    <div style="flex: 1; min-width: 200px; @media (max-width: 600px) { min-width: 100%; }">
                        <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Description</label>
                        <input type="text" name="description" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                    </div>
                </div>
                <div style="margin-top: 10px;">
                    <button style="display: inline-block; padding: 0.5rem 1rem; border: none; border-radius: 4px; font-size: 0.875rem; font-weight: 500; cursor: pointer; background: #2563eb; color: white; @media (max-width: 600px) { width: 100%; text-align: center; }">Créer</button>
                </div>
            </form>
            <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                <thead>
                    <tr>
                        <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid #eee; font-weight: 600; font-size: 0.875rem; background: #f9fafb;">Nom</th>
                        <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid #eee; font-weight: 600; font-size: 0.875rem; background: #f9fafb;">Membres</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($teams as $t)
                        <tr>
                            <td style="padding: 0.75rem; border-bottom: 1px solid #eee;">{{ $t->name }}</td>
                            <td style="padding: 0.75rem; border-bottom: 1px solid #eee; font-size: 0.875rem; color: #666;">
                                @forelse($t->users as $u)
                                    {{ $u->name }}@if (!$loop->last)
                                        ,
                                    @endif @empty —
                                @endforelse
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
            <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">Gérer les membres</h2>
            <form method="POST" action="{{ route('teams.attach', $teams->first() ?? 0) }}">
                @csrf
                <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
                    <div style="flex: 1; min-width: 200px; @media (max-width: 600px) { min-width: 100%; }">
                        <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Équipe</label>
                        <select name="team_id" onchange="this.form.action=this.form.action.replace(/teams\\/\\d+\\/attach/,'teams/'+this.value+'/attach')" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                            @foreach ($teams as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="flex: 1; min-width: 200px; @media (max-width: 600px) { min-width: 100%; }">
                        <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Membre</label>
                        <select name="user_id" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                            @foreach ($users as $u)
                                @if ($u->role === 'member')
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="margin-top: 10px;">
                    <button style="display: inline-block; padding: 0.5rem 1rem; border: none; border-radius: 4px; font-size: 0.875rem; font-weight: 500; cursor: pointer; background: #2563eb; color: white; @media (max-width: 600px) { width: 100%; text-align: center; }">Ajouter</button>
                </div>
            </form>

            <form method="POST" action="{{ route('teams.detach', $teams->first() ?? 0) }}" style="margin-top: 16px;">
                @csrf
                <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
                    <div style="flex: 1; min-width: 200px; @media (max-width: 600px) { min-width: 100%; }">
                        <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Équipe</label>
                        <select name="team_id" onchange="this.form.action=this.form.action.replace(/teams\\/\\d+\\/detach/,'teams/'+this.value+'/detach')" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                            @foreach ($teams as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="flex: 1; min-width: 200px; @media (max-width: 600px) { min-width: 100%; }">
                        <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Membre</label>
                        <select name="user_id" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                            @foreach ($users as $u)
                                @if ($u->role === 'member')
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="margin-top: 10px;">
                    <button style="display: inline-block; padding: 0.5rem 1rem; border: none; border-radius: 4px; font-size: 0.875rem; font-weight: 500; cursor: pointer; background: #b91c1c; color: white; @media (max-width: 600px) { width: 100%; text-align: center; }">Retirer</button>
                </div>
            </form>
        </div>
    </div>
@endsection
