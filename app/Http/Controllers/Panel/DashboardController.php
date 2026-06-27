<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\RequestModel;
use App\Services\RequestReferralService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        private RequestReferralService $referralService,
    ) {}

    public function index()
    {
        $user = Auth::user();

        $baseQuery = function () use ($user) {
            $q = RequestModel::query();
            if ($user->role === 'expert') {
                $q->where(function ($query) use ($user) {
                    $query->where('department_id', $user->department_id)
                          ->orWhere('assigned_expert_id', $user->id);
                });
            }
            return $q;
        };

        $stats = [
            'pending'  => $baseQuery()->where('status', 'pending')->count(),
            'seen'     => $baseQuery()->where('status', 'seen')->count(),
            'answered' => $baseQuery()->where('status', 'answered')->count(),
            'referred' => $baseQuery()->where('status', 'referred')->count(),
        ];

        $recentRequests = $baseQuery()
            ->with(['requester', 'department', 'assignedExpert'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('panel.dashboard', compact('stats', 'recentRequests', 'user'));
    }

    public function show($id)
    {
        $user    = Auth::user();
        $request = RequestModel::with(['requester', 'department', 'assignedExpert', 'files', 'replies.expert', 'referrals.fromExpert', 'referrals.toExpert'])
            ->findOrFail($id);

        if ($user->role === 'expert') {
            $belongsToDept = (int) $request->department_id === (int) $user->department_id;
            $assignedToMe  = (int) $request->assigned_expert_id === (int) $user->id;

            if (!$belongsToDept && !$assignedToMe) {
                abort(403, 'شما به این درخواست دسترسی ندارید.');
            }
        }

        if (is_null($request->assigned_expert_id) && (int) $request->department_id === (int) $user->department_id) {
            $request->update([
                'status'             => 'seen',
                'assigned_expert_id' => $user->id,
                'seen_at'            => now(),
            ]);
        } elseif (is_null($request->seen_at) && (int) $request->assigned_expert_id === (int) $user->id) {
            $request->update(['seen_at' => now()]);
        }

        $experts = \App\Models\User::where('role', 'expert')
            ->where('id', '!=', $user->id)
            ->where('is_active', true)
            ->get();

        return view('panel.request', compact('request', 'experts', 'user'));
    }

    public function reply(\Illuminate\Http\Request $httpRequest, $id)
    {
        $httpRequest->validate([
            'body' => 'required|string',
            'file' => 'nullable|file|max:10240',
        ]);

        $req = RequestModel::findOrFail($id);

        $filePath = null;
        $fileName = null;

        if ($httpRequest->hasFile('file')) {
            $file     = $httpRequest->file('file');
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('replies', 'public');
        }

        \App\Models\RequestReply::create([
            'request_id'  => $req->id,
            'expert_id'   => Auth::id(),
            'body'        => $httpRequest->body,
            'file_path'   => $filePath,
            'file_name'   => $fileName,
            'is_referral' => false,
        ]);

        $req->update([
            'status'      => 'answered',
            'answered_at' => now(),
        ]);

        // پاسخ نهایی یعنی این درخواست دیگر در حال ارجاع نیست؛ آخرین مرحله باز را می‌بندیم
        // تا گزارش زمان‌بندی (مدت ماندن نزد آخرین کارشناس) کامل باشد.
        $this->referralService->closeOpenStep($req);

        $this->notifyStudentByBale($req);

        return back()->with('success', 'پاسخ با موفقیت ارسال شد.');
    }

    public function refer(\Illuminate\Http\Request $httpRequest, $id)
    {
        $httpRequest->validate([
            'expert_id' => 'required|exists:users,id',
            'note'      => 'nullable|string',
        ]);

        $req = RequestModel::findOrFail($id);
        $fromExpert = Auth::user();
        $toExpert   = \App\Models\User::findOrFail($httpRequest->expert_id);

        $reply = \App\Models\RequestReply::create([
            'request_id'  => $req->id,
            'expert_id'   => $fromExpert->id,
            'body'        => $httpRequest->note ?? 'ارجاع داده شد.',
            'is_referral' => true,
            'referred_to' => $toExpert->id,
        ]);

        $req->update([
            'status'             => 'referred',
            'assigned_expert_id' => $toExpert->id,
            'referred_at'        => now(),
        ]);

        $this->referralService->recordStep(
            request: $req,
            fromExpert: $fromExpert,
            toExpert: $toExpert,
            type: 'referral',
            reply: $reply,
        );

        $this->notifyExpertByBale($req, $toExpert->id);

        return redirect()->route('panel.dashboard')->with('success', 'درخواست با موفقیت ارجاع داده شد.');
    }

    private function notifyStudentByBale(RequestModel $req): void
    {
        $student = $req->requester;
        if (!$student?->bale_id) return;

        $apiUrl = config('services.bale.api_url') . config('services.bale.token');

        \Illuminate\Support\Facades\Http::post("{$apiUrl}/sendMessage", [
            'chat_id' => $student->bale_id,
            'text'    =>
                "✅ درخواست شما پاسخ داده شد.\n\n" .
                "🔖 کد پیگیری: {$req->tracking_code}\n\n" .
                "برای مشاهده پاسخ روی «درخواست‌های قبلی» بزنید.",
        ]);
    }

    private function notifyExpertByBale(RequestModel $req, int $expertId): void
    {
        $expert = \App\Models\User::find($expertId);
        if (!$expert?->bale_id) return;

        $apiUrl = config('services.bale.api_url') . config('services.bale.token');

        \Illuminate\Support\Facades\Http::post("{$apiUrl}/sendMessage", [
            'chat_id' => $expert->bale_id,
            'text'    =>
                "📨 یک درخواست جدید به شما ارجاع داده شد.\n\n" .
                "🔖 کد پیگیری: {$req->tracking_code}\n" .
                "🏛 حوزه: {$req->department?->name}\n\n" .
                "برای مشاهده به پنل مراجعه کنید.",
        ]);
    }

    public function recallReferral($id)
    {
        $req  = RequestModel::findOrFail($id);
        $user = Auth::user();

        $lastReferral = $req->replies()->where('is_referral', true)->latest()->first();

        if (!$lastReferral || (int) $lastReferral->expert_id !== (int) $user->id) {
            abort(403, 'شما اجازه بازگشت این ارجاع را ندارید.');
        }

        $previousExpert = \App\Models\User::find($req->assigned_expert_id);

        $reply = \App\Models\RequestReply::create([
            'request_id'  => $req->id,
            'expert_id'   => $user->id,
            'body'        => 'ارجاع توسط ارجاع‌دهنده لغو شد.',
            'is_referral' => false,
        ]);

        $req->update([
            'status'             => 'seen',
            'assigned_expert_id' => $user->id,
            'referred_at'        => null,
        ]);

        $this->referralService->recordStep(
            request: $req,
            fromExpert: $previousExpert,
            toExpert: $user,
            type: 'recall',
            reply: $reply,
        );

        return redirect()->route('panel.dashboard')->with('success', 'ارجاع با موفقیت لغو شد.');
    }

    public function returnRequest($id)
    {
        $req  = RequestModel::findOrFail($id);
        $user = Auth::user();

        if ((int) $req->assigned_expert_id !== (int) $user->id) {
            abort(403, 'شما اجازه عودت این درخواست را ندارید.');
        }

        $lastReferral = $req->replies()->where('is_referral', true)->latest()->first();
        $returnToId   = $lastReferral?->expert_id;

        if (!$returnToId) {
            return back()->with('error', 'ارجاع‌دهنده یافت نشد.');
        }

        $returnTo = \App\Models\User::findOrFail($returnToId);

        $reply = \App\Models\RequestReply::create([
            'request_id'  => $req->id,
            'expert_id'   => $user->id,
            'body'        => 'درخواست به ارجاع‌دهنده عودت داده شد.',
            'is_referral' => true,
            'referred_to' => $returnTo->id,
        ]);

        $req->update([
            'status'             => 'referred',
            'assigned_expert_id' => $returnTo->id,
            'referred_at'        => now(),
        ]);

        $this->referralService->recordStep(
            request: $req,
            fromExpert: $user,
            toExpert: $returnTo,
            type: 'return',
            reply: $reply,
        );

        $this->notifyExpertByBale($req, $returnTo->id);

        return redirect()->route('panel.dashboard')->with('success', 'درخواست با موفقیت عودت داده شد.');
    }

    public function allRequests(\Illuminate\Http\Request $httpRequest)
    {
        $user = Auth::user();

        $query = RequestModel::with(['requester', 'department', 'assignedExpert'])
            ->orderBy('created_at', 'desc');

        // فیلتر مخصوص گزارش کارشناس — لینک از صفحه گزارشات
        if ($httpRequest->filled('expert_id')) {
            $expertId = $httpRequest->expert_id;
            $from     = $httpRequest->from_date;
            $to       = $httpRequest->to_date;

            $assignedIds = RequestModel::where('assigned_expert_id', $expertId)
                ->whereDate('created_at', '>=', $from)
                ->whereDate('created_at', '<=', $to)
                ->pluck('id');

            $repliedIds = \App\Models\RequestReply::where('expert_id', $expertId)
                ->whereDate('created_at', '>=', $from)
                ->whereDate('created_at', '<=', $to)
                ->pluck('request_id');

            $allIds = $assignedIds->merge($repliedIds)->unique();

            $query->whereIn('id', $allIds);

        } elseif ($httpRequest->filled('department_id') && $httpRequest->filled('from_date') && $httpRequest->routeIs('panel.requests.index') && $httpRequest->filled('to_date') && !$httpRequest->filled('status') && !$httpRequest->filled('tracking_code')) {
            // این شرط برای جلوگیری از تداخل با فیلتر معمولی department_id در صفحه requests نیست؛
            // پس به جای شرط پیچیده، یک پارامتر جدا برای گزارش حوزه استفاده می‌کنیم: report_department
        }

        // دسترسی پایه بر اساس نقش (فقط وقتی از مسیر معمولی آمده، نه از گزارش)
        if (!$httpRequest->filled('expert_id') && !$httpRequest->filled('report_department')) {
            if ($user->role === 'expert') {
                $query->where(function ($q) use ($user) {
                    $q->where('department_id', $user->department_id)
                      ->orWhere('assigned_expert_id', $user->id);
                });
            } elseif ($user->role === 'admin' && $user->department_id) {
                $query->where('department_id', $user->department_id);
            }
        }

        // فیلتر مخصوص گزارش حوزه
        if ($httpRequest->filled('report_department')) {
            $query->where('department_id', $httpRequest->report_department)
                  ->whereDate('created_at', '>=', $httpRequest->from_date)
                  ->whereDate('created_at', '<=', $httpRequest->to_date);
        }

        // فیلترهای معمولی صفحه درخواست‌ها
        if ($httpRequest->filled('status')) {
            $query->where('status', $httpRequest->status);
        }
        if ($httpRequest->filled('department_id') && !$httpRequest->filled('expert_id')) {
            $query->where('department_id', $httpRequest->department_id);
        }
        if ($httpRequest->filled('tracking_code')) {
            $query->where('tracking_code', 'like', '%' . $httpRequest->tracking_code . '%');
        }
        if ($httpRequest->filled('from_date') && !$httpRequest->filled('expert_id') && !$httpRequest->filled('report_department')) {
            $query->whereDate('created_at', '>=', $httpRequest->from_date);
        }
        if ($httpRequest->filled('to_date') && !$httpRequest->filled('expert_id') && !$httpRequest->filled('report_department')) {
            $query->whereDate('created_at', '<=', $httpRequest->to_date);
        }

        $requests = $query->paginate(15)->withQueryString();

        $departments = \App\Models\Department::where('is_active', true)->get();

        return view('panel.requests', compact('requests', 'departments', 'user'));
    }
}
