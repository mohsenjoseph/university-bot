<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\RequestModel;
use App\Models\RequestReferral;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            $referralReports   = $this->buildReferralReport($fromDate, $toDate);
        } else {
            $expertReports     = $this->buildExpertReport($fromDate, $toDate, $user->id);
            $departmentReports = collect();
            $referralReports   = $this->buildReferralReport($fromDate, $toDate, $user->id);
        }

        return view('panel.reports', compact('expertReports', 'departmentReports', 'referralReports', 'fromDate', 'toDate', 'user'));
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

    /**
     * گزارش تحلیلی ارجاع‌ها بر اساس جدول request_referrals:
     * - تعداد ارجاع داده‌شده و گرفته‌شده توسط هر کارشناس
     * - میانگین زمانی که هر درخواست نزد یک کارشناس مانده (به ساعت)
     * - طولانی‌ترین مسیر ارجاع (بیشترین تعداد مرحله) در بازه انتخابی
     */
    private function buildReferralReport(string $fromDate, string $toDate, ?int $onlyExpertId = null)
    {
        $stepsQuery = RequestReferral::query()
            ->whereDate('started_at', '>=', $fromDate)
            ->whereDate('started_at', '<=', $toDate);

        if ($onlyExpertId) {
            $stepsQuery->where(function ($q) use ($onlyExpertId) {
                $q->where('from_expert_id', $onlyExpertId)
                  ->orWhere('to_expert_id', $onlyExpertId);
            });
        }

        $steps = $stepsQuery->get();

        $sentByExpert = $steps->whereNotNull('from_expert_id')
            ->groupBy('from_expert_id')
            ->map->count();

        $receivedByExpert = $steps->groupBy('to_expert_id')->map->count();

        $expertIds = $sentByExpert->keys()->merge($receivedByExpert->keys())->unique();

        $perExpert = $expertIds->map(function ($expertId) use ($sentByExpert, $receivedByExpert) {
            $expert = User::find($expertId);
            return [
                'expert_id' => $expertId,
                'name'      => $expert?->name ?? '-',
                'sent'      => $sentByExpert->get($expertId, 0),
                'received'  => $receivedByExpert->get($expertId, 0),
            ];
        })->values();

        // میانگین مدت زمان هر مرحله (فقط مراحلی که بسته شده‌اند، یعنی closed_at دارند)
        $closedSteps = $steps->whereNotNull('closed_at');
        $avgStepHours = $closedSteps->isNotEmpty()
            ? round($closedSteps->avg(fn ($s) => $s->started_at->diffInMinutes($s->closed_at)) / 60, 1)
            : null;

        // طولانی‌ترین مسیر ارجاع در بازه: درخواستی با بیشترین step_no
        $longestChain = RequestReferral::query()
            ->whereDate('started_at', '>=', $fromDate)
            ->whereDate('started_at', '<=', $toDate)
            ->select('request_id', DB::raw('MAX(step_no) as max_step'))
            ->groupBy('request_id')
            ->orderByDesc('max_step')
            ->first();

        $longestChainRequest = $longestChain
            ? RequestModel::find($longestChain->request_id)
            : null;

        return [
            'per_expert'            => $perExpert,
            'avg_step_hours'        => $avgStepHours,
            'longest_chain_steps'   => $longestChain?->max_step,
            'longest_chain_request' => $longestChainRequest,
            'total_referral_steps'  => $steps->count(),
        ];
    }
}
