<?php

namespace App\Http\Controllers;

use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use App\Traits\Responses;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    use Responses;

    public function __construct(protected TaskService $taskService) {}

    /**
     * @group Tasks
     * @summary List all tasks for a project (with filters)
     * @authenticated
     * @urlParam project_id integer required The project ID. Example: 1
     * @queryParam status string Filter by status (todo, in_progress, done). Example: todo
     * @queryParam priority string Filter by priority (low, medium, high). Example: high
     * @queryParam search string Search by title. Example: bug
     * @response 200 scenario="Success" {
     *   "status": 200,
     *   "message": "Tasks retrieved successfully",
     *   "data": {
     *     "items": [
     *       {
     *         "id": 1,
     *         "title": "Fix login bug",
     *         "description": "The login button is not working on mobile.",
     *         "priority": "high",
     *         "status": "todo",
     *         "due_date": "2026-08-10",
     *         "created_at": "2026-08-02 10:00:00",
     *         "updated_at": "2026-08-02 10:00:00"
     *       }
     *     ]
     *   },
     *   "pagination": {
     *     "current_page": 1,
     *     "per_page": 10,
     *     "total": 1,
     *     "last_page": 1,
     *     "next_page_url": null,
     *     "prev_page_url": null,
     *     "current_page_url": "http://127.0.0.1:8000/api/projects/1/tasks?page=1"
     *   }
     * }
     * @response 403 scenario="Forbidden" {
     *   "status": 403,
     *   "message": "Forbidden"
     * }
     * @response 404 scenario="Not Found" {
     *   "status": 404,
     *   "message": "Project not found"
     * }
     */
    public function index(int $project_id)
    {
        $project = Project::find($project_id);

        if (!$project) {
            return $this->error(status: 404, message: 'Project not found');
        }

        if ($project->user_id !== auth()->id()) {
            return $this->error(status: 403, message: 'Forbidden');
        }

        $tasks = $this->taskService->index($project, request()->only(['status', 'priority', 'search']));

        return $this->successPaginated(
            status: Response::HTTP_OK,
            message: "Projects retrieved successfully",
            data: TaskResource::collection($tasks)
        );
    }

    /**
     * @group Tasks
     * @summary Create a new task in a project
     * @authenticated
     * @urlParam project_id integer required The project ID. Example: 1
     * @response 201 scenario="Success" {
     *   "status": 201,
     *   "message": "Task created successfully",
     *   "data": {
     *     "id": 1,
     *     "title": "Fix login bug",
     *     "description": "The login button is not working on mobile.",
     *     "priority": "high",
     *     "status": "todo",
     *     "due_date": "2026-08-10",
     *     "created_at": "2026-08-02 10:00:00",
     *     "updated_at": "2026-08-02 10:00:00"
     *   }
     * }
     * @response 403 scenario="Forbidden" {
     *   "status": 403,
     *   "message": "Forbidden"
     * }
     * @response 404 scenario="Not Found" {
     *   "status": 404,
     *   "message": "Project not found"
     * }
     */
    public function store(StoreTaskRequest $request, int $project_id)
    {
        $project = Project::find($project_id);

        if (!$project) {
            return $this->error(status: 404, message: 'Project not found');
        }

        if ($project->user_id !== auth()->id()) {
            return $this->error(status: 403, message: 'Forbidden');
        }

        $task = $this->taskService->store($project, $request->validated());

        return $this->success(
            status: 201,
            message: 'Task created successfully',
            data: new TaskResource($task),
        );
    }

    /**
     * @group Tasks
     * @summary Update a task
     * @authenticated
     * @urlParam project_id integer required The project ID. Example: 1
     * @urlParam id integer required The task ID. Example: 1
     * @response 200 scenario="Success" {
     *   "status": 200,
     *   "message": "Task updated successfully",
     *   "data": {
     *     "id": 1,
     *     "title": "Updated task title",
     *     "description": "Updated description.",
     *     "priority": "medium",
     *     "status": "in_progress",
     *     "due_date": "2026-08-15",
     *     "created_at": "2026-08-02 10:00:00",
     *     "updated_at": "2026-08-02 10:30:00"
     *   }
     * }
     * @response 403 scenario="Forbidden" {
     *   "status": 403,
     *   "message": "Forbidden"
     * }
     * @response 404 scenario="Not Found" {
     *   "status": 404,
     *   "message": "Task not found"
     * }
     */
    public function update(UpdateTaskRequest $request, int $project_id, int $id)
    {
        $project = Project::find($project_id);

        if (!$project) {
            return $this->error(status: 404, message: 'Project not found');
        }

        if ($project->user_id !== auth()->id()) {
            return $this->error(status: 403, message: 'Forbidden');
        }

        $task = Task::where('project_id', $project_id)->find($id);

        if (!$task) {
            return $this->error(status: 404, message: 'Task not found');
        }

        $updated = $this->taskService->update($task, $request->validated());

        return $this->success(
            status: 200,
            message: 'Task updated successfully',
            data: new TaskResource($updated),
        );
    }

    /**
     * @group Tasks
     * @summary Delete a task
     * @authenticated
     * @urlParam project_id integer required The project ID. Example: 1
     * @urlParam id integer required The task ID. Example: 1
     * @response 200 scenario="Success" {
     *   "status": 200,
     *   "message": "Task deleted successfully"
     * }
     * @response 403 scenario="Forbidden" {
     *   "status": 403,
     *   "message": "Forbidden"
     * }
     * @response 404 scenario="Not Found" {
     *   "status": 404,
     *   "message": "Task not found"
     * }
     */
    public function destroy(int $project_id, int $id)
    {
        $project = Project::find($project_id);

        if (!$project) {
            return $this->error(status: 404, message: 'Project not found');
        }

        if ($project->user_id !== auth()->id()) {
            return $this->error(status: 403, message: 'Forbidden');
        }

        $task = Task::where('project_id', $project_id)->find($id);

        if (!$task) {
            return $this->error(status: 404, message: 'Task not found');
        }

        $this->taskService->destroy($task);

        return $this->success(
            status: 200,
            message: 'Task deleted successfully',
        );
    }
}
