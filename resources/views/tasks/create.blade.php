{{--
    US9 — Create task form (lead only)
    Extends: layouts/app.blade.php
    Controller: TaskController@create (GET) + TaskController@store (POST)
    Route: GET /projects/{project}/tasks/create
           POST /projects/{project}/tasks

    Variables available:
        $project → Project model (needed for the form action URL)
        $members → Collection<User> — project members only (for assignee dropdown)

    IMPORTANT: $members contains ONLY project members, not all users.
    The StoreTaskRequest also validates that assigned_to is a project member.
    This is double validation: UI (dropdown only shows members) + server (Rule::exists).
--}}
{{-- @extends('layouts.app')
@section('title', 'Nouvelle tâche')

@section('content') --}}
<x-app-layout>
<div style="max-width:560px; margin:0 auto;">

    {{-- ── Page header ──────────────────────────────────────────────────── --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Nouvelle tâche</h1>
            <p class="page-subtitle">
                Projet :
                <a href="{{ route('projects.show', $project) }}"
                   style="color:#6366f1; text-decoration:none;">
                    {{ $project->title }}
                </a>
            </p>
        </div>
        <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary">Annuler</a>
    </div>

    {{-- ── Form card ────────────────────────────────────────────────────── --}}
    <div class="card">
        {{--
            action: POST /projects/{project}/tasks → TaskController@store
            The {project} parameter is available in StoreTaskRequest
            via $this->route('project') for the assigned_to pivot check.
        --}}
        <form method="POST" action="{{ route('projects.tasks.store', $project) }}">
            @csrf

            {{-- Title --}}
            <div class="form-group">
                <label class="form-label" for="title">
                    Titre de la tâche <span class="form-required">*</span>
                </label>
                <input
                    class="form-input"
                    id="title"
                    name="title"
                    type="text"
                    value="{{ old('title') }}"
                    placeholder="Ex : Implémenter l'authentification"
                    autofocus
                    maxlength="255"
                    required
                >
                @error('title')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div class="form-group">
                <label class="form-label" for="description">Description</label>
                <textarea
                    class="form-textarea"
                    id="description"
                    name="description"
                    placeholder="Détails, critères d'acceptation, ressources utiles…"
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Deadline + Priority in two columns --}}
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label" for="deadline">Deadline</label>
                    <input
                        class="form-input"
                        id="deadline"
                        name="deadline"
                        type="date"
                        value="{{ old('deadline') }}"
                    >
                    @error('deadline')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="priority">
                        Priorité <span class="form-required">*</span>
                    </label>
                    <select class="form-select" id="priority" name="priority" required>
                        <option value="">— Choisir —</option>
                        <option value="low"
                            {{ old('priority') === 'low' ? 'selected' : '' }}>
                            🟢 Basse
                        </option>
                        <option value="medium"
                            {{ old('priority') === 'medium' ? 'selected' : '' }}>
                            🟡 Moyenne
                        </option>
                        <option value="high"
                            {{ old('priority') === 'high' ? 'selected' : '' }}>
                            🔴 Haute
                        </option>
                    </select>
                    @error('priority')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Assignee dropdown — project members ONLY --}}
            <div class="form-group">
                <label class="form-label" for="assigned_to">
                    Assigné à <span class="form-required">*</span>
                </label>
                {{--
                    Only $members (project members) are shown — not all users.
                    The StoreTaskRequest validates: assigned_to must exist
                    in project_members for this project.
                --}}
                <select class="form-select" id="assigned_to" name="assigned_to" required>
                    <option value="">— Choisir un développeur —</option>
                    @forelse ($members as $member)
                        <option value="{{ $member->id }}"
                            {{ old('assigned_to') == $member->id ? 'selected' : '' }}>
                            {{ $member->name }} ({{ $member->email }})
                        </option>
                    @empty
                        <option value="" disabled>Aucun membre dans ce projet</option>
                    @endforelse
                </select>
                @if ($members->isEmpty())
                    <p class="form-hint">
                        ⚠ Ajoutez d'abord des membres au projet avant de créer des tâches.
                    </p>
                @endif
                @error('assigned_to')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- ── Form actions ──────────────────────────────────────────── --}}
            <div class="flex-between" style="margin-top:1.5rem;">
                <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary">
                    Annuler
                </a>
                <button type="submit" class="btn btn-primary"
                        {{ $members->isEmpty() ? 'disabled' : '' }}>
                    Créer la tâche
                </button>
            </div>

        </form>
    </div>

</div>
</x-app-layout>

