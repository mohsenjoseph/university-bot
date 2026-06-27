<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\RequestModel;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $fromDate = $request->filled('from_date') ? $request->from_date : now()->subMonth()->format('Y-m-d');
        $toDate   = $request->filled('to_date')   ? $request->to_date   : now()->format('Y-m-d');

        if ($user->role === 'admin') {
            $expertReports     = $this->buildExpertReport($fromDate, $toDate);
            $departmentReports = $this->buildDepartmentReport($fromDate, $toDate);
        } else {
            $expertReports     = $this->buildExpertReport($fromDate, $toDate, $user->id);
            $departmentReports = collect();
        }

        return view('panel.reports', compact('expertReports', 'departmentReports', 'fromDate', 'toDate', 'user'));
    }

    private function buildExpertReport(string $fromDate, string $toDate, ?int $onlyExpertId = null)
    {
        $query = User::where('role', 'expert')->where('is_active', true);

        if ($onlyExpertId) {
            $query->where('id', $onlyExpertId);
        }

        $experts = $query->get();

        return $experts->map(function ($expert) use ($fromDate, $toDate) {

            $assignedIds = RequestModel::where('assigned_expert_id', $expert->id)
                ->whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->pluck('id');

            $repliedIds = \App\Models\RequestReply::where('expert_id', $expert->id)
                ->whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate)
                ->pluck('request_id');

            $allIds = $assignedIds->merge($repliedIds)->unique();

            $assignedNow = RequestModel::whereIn('id', $assignedIds);

            return [
                'name'      => $expert->name,
                'expert_id' => $expert->id,
                'from_date' => $fromDate,
                'to_date'   => $toDate,
                'pending'   => (clone $assignedNow)->where('status', 'pending')->count(),
                'seen'      => (clone $assignedNow)->where('status', 'seen')->count(),
                'answered'  => \App\Models\RequestReply::where('expert_id', $expert->id)
                                  ->where('is_referral', false)
                                  ->whereIn('request_id', $allIds)
                                  ->distinct('request_id')
                                  ->count('request_id'),
                'referred'  => \App\Models\RequestReply::where('expert_id', $expert->id)
                                  ->where('is_referral', true)
                                  ->whereIn('request_id', $allIds)
                                  ->distinct('request_id')
                                  ->count('request_id'),
                'total'     => $allIds->count(),
            ];
        });
    }

    private function buildDepartmentReport(string $fromDate, string $toDate)
    {
        $departments = Department::where('is_active', true)->get();

        return $departments->map(function ($dept) use ($fromDate, $toDate) {
            $base = RequestModel::where('department_id', $dept->id)
                ->whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate);

            return [
                'name'          => $dept->name,
                'department_id' => $dept->id,
                'from_date'     => $fromDate,
                'to_date'       => $toDate,
                'pending'       => (clone $base)->where('status', 'pending')->count(),
                'seen'          => (clone $base)->where('status', 'seen')->count(),
                'answered'      => (clone $base)->where('status', 'answered')->count(),
                'referred'      => (clone $base)->where('status', 'referred')->count(),
                'total'         => (clone $base)->count(),
            ];
        });
    }
}