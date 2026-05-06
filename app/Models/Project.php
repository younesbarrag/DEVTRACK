<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'description', 'deadline', 'user_id'];

    //  Mutator : titre en ucfirst
    protected function title(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            set: fn (string $value) => ucfirst($value),
        );
    }

    // Lead créateur
    public function lead()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Tous les membres (lead + developers)
    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    // Tâches du projet
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    // Tâches terminées
    public function completedTasks()
    {
        return $this->tasks()->where('status', 'done');
    }

    // Scope : projets actifs (non archivés)
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    // Scope : projets archivés
    public function scopeArchived($query)
    {
        return $query->whereNotNull('deleted_at');
    }
}