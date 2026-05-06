<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Projets dont l'utilisateur est membre (lead ou developer)
    public function projects()
    {
        return $this->belongsToMany(Project::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    // Projets où il est lead
    public function leadProjects()
    {
        return $this->hasMany(Project::class);
    }

    // Tâches assignées
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    // Vérifier si lead d'un projet
    public function isLead(Project $project): bool
    {
        return $this->projects()
            ->where('project_id', $project->id)
            ->wherePivot('role', 'lead')
            ->exists();
    }

    // Vérifier si membre d'un projet
    public function isMember(Project $project): bool
    {
        return $this->projects()
            ->where('project_id', $project->id)
            ->exists();
    }
}