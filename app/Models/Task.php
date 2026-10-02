<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

protected $fillable = [
        'title', 'description', 'status', 'priority', 'deadline', 'project_id', 'assigned_to', 'user_id',
    ];

    //  Accessor : statut formaté pour l'API
    protected function statusLabel(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => match($this->status) {
                'todo' => 'À faire',
                'in_progress' => 'En cours',
                'done' => 'Terminé',
                default => $this->status,
            },
        );
    }

    //  Accessor : statut de la deadline (urgent ou pas)
    protected function deadlineStatus(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: function () {
                if ($this->status === 'done') return 'ok';
                $hoursLeft = now()->diffInHours($this->deadline, false);
                if ($hoursLeft < 0) return 'overdue';
                if ($hoursLeft <= 48) return 'urgent';
                return 'normal';
            },
        );
    }

    //  Scope : tâches urgentes (deadline < 48h et pas done)
    public function scopeUrgent($query)
    {
        return $query->where('status', '!=', 'done')
            ->where('deadline', '<=', now()->addHours(48));
    }

    // Projet parent
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Developer assigné
    public function user()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Créateur de la tâche (celui qui l'a ajoutée au projet)
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scope : tâches assignées à un developer donné
    public function scopeAssignedTo($query, User $user)
    {
        return $query->where('assigned_to', $user->id);
    }

    // Vérifier si urgente
    public function isUrgent(): bool
    {
        // Sans deadline, une tâche n'est jamais urgente.
        return $this->status !== 'done'
            && $this->deadline !== null
            && $this->deadline <= now()->addHours(48);
    }
}
