{{--
    US10 — Edit task form (lead only)
    Extends: layouts/app.blade.php
    Controller: TaskController@edit (GET) + TaskController@update (PATCH)
    Route: GET /tasks/{task}/edit + PATCH /tasks/{task}

    Variables available:
        $task    → Task model (current values to pre-fill)
        $members → Collection<User> — project members for the assignee dropdown

    Only the lead can reach this view (TaskPolicy::update() in controller).
    Developer trying to access this URL → 403.

    STATUS IS NOT HERE — developers change status via a separate route:
        PATCH /tasks/{task}/status → TaskController@updateStatus
--}}
<x-app-layout>
<div style="max-width:560px; margin:0 auto;">

    {{-- ── Page header ──────────────────────────────────────────────────── --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Modifier la tâche</h1>
            <p class="page-subtitle">
                Projet :
                <a href="{{ route('projects.show', $task->project) }}"
                   style="color:#6366f1; text-decoration:none;">
                    {{ $task->project->title }}
                </a>
            </p>
        </div>
        <a href="{{ route('projects.show', $task->project) }}" class="btn btn-secondary">
            Annuler
        </a>
    </div>

    {{-- Current status indicator (read-only — not editable here) --}}
    <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px;
                padding:10px 14px; margin-bottom:1rem;
                display:flex; align-items:center; gap:8px; font-size:13px; color:#6b7280;">
        <span>Statut actuel :</span>
        {{-- formatted_status and status_color are accessors on Task model --}}
        <span class="badge {{ $task->status_color }}">{{ $task->formatted_status }}</span>
        <span style="margin-left:auto; font-size:12px; color:#9ca3af;">
            Le statut est modifié par le développeur assigné
        </span>
    </div>

    {{-- ── Form card ────────────────────────────────────────────────────── --}}
    <div class="card">
        {{--
            @method('PATCH'): HTML doesn't support PATCH natively.
            This hidden field makes Laravel route this to update() not store().
        --}}
        <form method="POST" action="{{ route('tasks.update', $task) }}">
            @csrf
            @method('PATCH')

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
                    {{--
                        old('title', $task->title):
                        - Failed submit: old('title') re-fills what user typed
                        - First visit: $task->title fills with current value
                    --}}
                    value="{{ old('title', $task->title) }}"
                    maxlength="255"
                    autofocus
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
                >{{ old('description', $task->description) }}</textarea>
                @error('description')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Deadline + Priority --}}
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label" for="deadline">Deadline</label>
                    <input
                        class="form-input"
                        id="deadline"
                        name="deadline"
                        type="date"
                        value="{{ old('deadline', $task->deadline?->format('Y-m-d')) }}"
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
                        {{--
                            old('priority', $task->priority):
                            First visit pre-selects the current task priority.
                        --}}
                        <option value="low"
                            {{ old('priority', $task->priority) === 'low' ? 'selected' : '' }}>
                            🟢 Basse
                        </option>
                        <option value="medium"
                            {{ old('priority', $task->priority) === 'medium' ? 'selected' : '' }}>
                            🟡 Moyenne
                        </option>
                        <option value="high"
                            {{ old('priority', $task->priority) === 'high' ? 'selected' : '' }}>
                            🔴 Haute
                        </option>
                    </select>
                    @error('priority')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Assignee --}}
            <div class="form-group">
                <label class="form-label" for="assigned_to">
                    Assigné à <span class="form-required">*</span>
                </label>
                <select class="form-select" id="assigned_to" name="assigned_to" required>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}"
                            {{--
                                Pre-select the currently assigned developer.
                                old() takes priority on failed re-submit.
                            --}}
                            {{ old('assigned_to', $task->assigned_to) == $member->id ? 'selected' : '' }}>
                            {{ $member->name }} ({{ $member->email }})
                        </option>
                    @endforeach
                </select>
                @error('assigned_to')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- ── Form actions ──────────────────────────────────────────── --}}
            <div class="flex-between" style="margin-top:1.5rem;">
                <a href="{{ route('projects.show', $task->project) }}"
                   class="btn btn-secondary">
                    Annuler
                </a>
                <button type="submit" class="btn btn-primary">
                    Enregistrer les modifications
                </button>
            </div>

        </form>
    </div>

</div>
</x-app-layout>

