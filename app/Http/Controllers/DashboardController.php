<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Traits\Responses;

class DashboardController extends Controller
{
    use Responses;

    public function __construct(protected DashboardService $dashboardService) {}

    /**
     * @group Dashboard
     * @summary Get dashboard statistics
     * @authenticated
     * @response 200 scenario="Success" {
     *   "status": 200,
     *   "message": "Dashboard statistics retrieved successfully",
     *   "data": {
     *     "total_projects": 5,
     *     "active_projects": 3,
     *     "total_tasks": 20,
     *     "completed_tasks": 8,
     *     "pending_tasks": 10,
     *     "overdue_tasks": 2
     *   }
     * }
     * @response 401 scenario="Unauthenticated" {
     *   "status": 401,
     *   "message": "Unauthenticated."
     * }
     */
    public function index()
    {
        $stats = $this->dashboardService->getStats();

        return $this->success(
            status: 200,
            message: 'Dashboard statistics retrieved successfully',
            data: $stats,
        );
    }
}
