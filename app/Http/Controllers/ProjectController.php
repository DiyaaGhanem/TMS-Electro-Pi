<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use App\Traits\Responses;
use Illuminate\Http\Response;

class ProjectController extends Controller
{
    use Responses;

    public function __construct(protected ProjectService $projectService) {}

    /**
     * @group Projects
     * @summary List all projects for authenticated user
     * @authenticated
     * @response 200 scenario="Success" {
     *   "status": 200,
     *   "message": "Projects retrieved successfully",
     *   "data": {
     *     "items": [
     *       {
     *         "id": 1,
     *         "name": "TMS Project",
     *         "description": "Task management project",
     *         "status": "active",
     *         "tasks_count": 5,
     *         "created_at": "2026-08-02 10:00:00",
     *         "updated_at": "2026-08-02 10:00:00"
     *       }
     *     ],
     *     "extra": []
     *   },
     *   "pagination": {
     *     "current_page": 1,
     *     "per_page": 10,
     *     "total": 3,
     *     "last_page": 1,
     *     "next_page_url": null,
     *     "prev_page_url": null,
     *     "current_page_url": "http://127.0.0.1:8000/api/projects?page=1"
     *   }
     * }
     * @response 401 scenario="Unauthenticated" {
     *   "status": 401,
     *   "message": "Unauthenticated."
     * }
     */
    public function index()
    {
        $projects = $this->projectService->index();

        return $this->successPaginated(
            status: Response::HTTP_OK,
            message: "Projects retrieved successfully",
            data: ProjectResource::collection($projects)
        );
    }

    /**
     * @group Projects
     * @summary Create a new project
     * @authenticated
     * @response 201 scenario="Success" {
     *   "status": 201,
     *   "message": "Project created successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "TMS Project",
     *     "description": "Task management project",
     *     "status": "active",
     *     "tasks_count": 0,
     *     "created_at": "2026-08-02 10:00:00",
     *     "updated_at": "2026-08-02 10:00:00"
     *   }
     * }
     * @response 422 scenario="Validation Error" {
     *   "status": 422,
     *   "message": "The name field is required.",
     *   "errors": {
     *     "name": ["The name field is required."]
     *   }
     * }
     */
    public function store(StoreProjectRequest $request)
    {
        $project = $this->projectService->store($request->validated());

        return $this->success(
            status: 201,
            message: 'Project created successfully',
            data: new ProjectResource($project),
        );
    }

    /**
     * @group Projects
     * @summary Get a specific project with its tasks
     * @authenticated
     * @urlParam id integer required The project ID. Example: 1
     * @response 200 scenario="Success" {
     *   "status": 200,
     *   "message": "Project retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "TMS Project",
     *     "description": "Task management project",
     *     "status": "active",
     *     "tasks_count": 2,
     *     "tasks": [
     *       {
     *         "id": 1,
     *         "title": "First Task",
     *         "description": "Task description",
     *         "priority": "high",
     *         "status": "todo",
     *         "due_date": "2026-08-10",
     *         "created_at": "2026-08-02 10:00:00",
     *         "updated_at": "2026-08-02 10:00:00"
     *       }
     *     ],
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
    public function show(int $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return $this->error(status: 404, message: 'Project not found');
        }
        if ($project->user_id !== auth()->id()) {
            return $this->error(status: 403, message: 'Forbidden');
        }

        return $this->success(
            status: 200,
            message: 'Project retrieved successfully',
            data: new ProjectResource($this->projectService->show($project)),
        );
    }

    /**
     * @group Projects
     * @summary Update a project
     * @authenticated
     * @urlParam id integer required The project ID. Example: 1
     * @response 200 scenario="Success" {
     *   "status": 200,
     *   "message": "Project updated successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "Updated Project",
     *     "description": "Updated description",
     *     "status": "completed",
     *     "tasks_count": 5,
     *     "created_at": "2026-08-02 10:00:00",
     *     "updated_at": "2026-08-02 10:00:00"
     *   }
     * }
     * @response 403 scenario="Forbidden" {
     *   "status": 403,
     *   "message": "Forbidden"
     * }
     */
    public function update(UpdateProjectRequest $request, int $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return $this->error(status: 404, message: 'Project not found');
        }
        if ($project->user_id !== auth()->id()) {
            return $this->error(status: 403, message: 'Forbidden');
        }

        $updated = $this->projectService->update($project, $request->validated());

        return $this->success(
            status: 200,
            message: 'Project updated successfully',
            data: new ProjectResource($updated),
        );
    }

    /**
     * @group Projects
     * @summary Delete a project
     * @authenticated
     * @urlParam id integer required The project ID. Example: 1
     * @response 200 scenario="Success" {
     *   "status": 200,
     *   "message": "Project deleted successfully"
     * }
     * @response 403 scenario="Forbidden" {
     *   "status": 403,
     *   "message": "Forbidden"
     * }
     */
    public function destroy(int $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return $this->error(status: 404, message: 'Project not found');
        }

        if ($project->user_id !== auth()->id()) {
            return $this->error(status: 403, message: 'Forbidden');
        }

        $this->projectService->destroy($project);

        return $this->success(
            status: 200,
            message: 'Project deleted successfully',
        );
    }
}
