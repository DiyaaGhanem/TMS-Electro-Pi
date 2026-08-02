<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class ProjectService
{
    public function index()
    {
        return Auth::user()
            ->projects()
            ->withCount('tasks')
            ->paginate(10);
    }

    public function store(array $data): Project
    {
        return Auth::user()->projects()->create($data);
    }

    public function show(Project $project): Project
    {
        return $project->loadCount('tasks')->load('tasks');
    }

    public function update(Project $project, array $data): Project
    {
        $project->update($data);
        return $project;
    }

    public function destroy(Project $project): void
    {
        $project->delete();
    }
}
