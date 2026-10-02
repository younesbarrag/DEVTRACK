<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    // public function viewAny(User $user): bool
    // {
    //     return false;
    // }

    /**
     * Determine whether the user can view the model.
     *
     * Le lead voit toutes les tâches de ses projets. Un developer ne voit que
     * les tâches qui lui sont assignées : sans cette restriction, n'importe quel
     * membre du projet pourrait ouvrir les tâches d'un autre developer en
     * modifiant l'URL.
     */
    public function view(User $user, Task $task): bool
    {
        return $user->isLead($task->project)
            || ($task->assigned_to === $user->id && $user->isMember($task->project));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    public function updateStatus(User $user, Task $task): bool
    {
        return $task->assigned_to === $user->id && $user->isMember($task->project);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        return $user->isLead($task->project);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->isLead($task->project);
    }
}
