<?php
namespace App\Http\Controllers;
use App\Models\{Applicant, Attendance, Employee, JobOpening, LeaveRequest, PerformanceReview};

class DashboardController extends Controller
{
    public function __invoke()
    {
        $today = today()->toDateString();
        return [
            'employees' => Employee::where('status', 'active')->count(),
            'present_today' => Attendance::where('date', $today)->whereIn('status', ['present', 'late'])->count(),
            'late_today' => Attendance::where('date', $today)->where('status', 'late')->count(),
            'pending_leaves' => LeaveRequest::where('status', 'pending')->count(),
            'open_jobs' => JobOpening::where('status', 'open')->count(),
            'active_applicants' => Applicant::whereNotIn('stage', ['hired', 'rejected'])->count(),
            'avg_rating' => round((float) PerformanceReview::avg('rating'), 1),
        ];
    }
}
