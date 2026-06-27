<?php

namespace App\Http\Controllers;

use App\Services\BotService;
use Illuminate\Http\Request;
use App\Helpers\NumberHelper;
class BotController extends Controller
{
    private BotService $bot;

    public function __construct(BotService $bot)
    {
        $this->bot = $bot;
    }

private array $callbackStateMap = [
    'for_self'        => ['register_for_whom'],
    'for_other'       => ['register_for_whom'],
    'has_file'        => ['register_file'],
    'no_file'         => ['register_file'],
    'confirm_request' => ['confirm'],
    'cancel_request'  => ['confirm', 'main_menu'],
    'back_to_menu'    => ['viewing_requests', 'main_menu', 'confirm'],
];

private function validateCallback(string $callbackData, string $currentStep): bool
{
    // callback های wildcard (dept_ و req_page_)
    if (str_starts_with($callbackData, 'dept_')) {
        return $currentStep === 'register_department';
    }

    if (str_starts_with($callbackData, 'req_page_')) {
        return $currentStep === 'viewing_requests';
    }

    // callback های ثابت
    $allowedSteps = $this->callbackStateMap[$callbackData] ?? [];
    return in_array($currentStep, $allowedSteps);
}

public function handle(Request $request)
{
    $update   = $request->all();
    $message  = $update['message'] ?? null;
    $callback = $update['callback_query'] ?? null;

    if ($callback) {
        $this->handleCallback($callback);
        return response()->json(['ok' => true]);
    }

    if (!$message) return response()->json(['ok' => true]);

    $chatId  = $message['chat']['id'];
    $baleId  = $message['from']['id'];
    $text    = $message['text'] ?? '';
    $contact = $message['contact'] ?? null;
    $file    = $message['document'] ?? $message['photo'] ?? null;

    $session = $this->bot->getSession($baleId);

    // اگه contact فرستاد
    if ($contact) {
        $this->handleContact_received($chatId, $baleId, $contact, $message['from']);
        return response()->json(['ok' => true]);
    }

    // اگه فایل فرستاد
    if ($file && $session->step === 'register_file') {
        $this->stepFileReceived($chatId, $baleId, $file, $session);
        return response()->json(['ok' => true]);
    }

    match(true) {
        $text === '/start'               => $this->handleStart($chatId, $baleId, $message['from']),
        $text === '📝 ثبت درخواست'      => $this->handleRegister($chatId, $baleId),
        $text === '📋 درخواست‌های قبلی' => $this->handlePrevious($chatId, $baleId),
        $text === '📞 اطلاعات تماس'     => $this->handleContact($chatId),
        $text === '🌐 ورود به وب سایت'  => $this->handleWebsite($chatId),
        $text === '📊 آمار و اطلاعات' => $this->handleStats($chatId, $baleId),
        default                          => $this->handleStep($chatId, $baleId, $text, $session),
    };

    return response()->json(['ok' => true]);
}

private function handleCallback($callback): void
{
    $chatId  = $callback['message']['chat']['id'];
    $baleId  = $callback['from']['id'];
    $data    = $callback['data'];
    $msgId   = $callback['message']['message_id'];

    $session = $this->bot->getSession($baleId);

    // اعتبارسنجی — آیا این callback در state فعلی مجازه؟
    if (!$this->validateCallback($data, $session->step)) {
        // پیام قدیمی رو حذف کن
        $this->bot->deleteMessage($chatId, $msgId);

        // پیام خطا بده و state فعلی رو نشون بده
        $this->bot->sendMessage($chatId, '⚠️ این گزینه دیگر معتبر نیست.');
        $this->restoreCurrentStep($chatId, $baleId, $session);
        return;
    }

    // حوزه‌ها
    $departments = [
        'dept_education'  => '🎓 آموزش',
        'dept_finance'    => '💰 مالی',
        'dept_dormitory'  => '🏠 خوابگاه',
        'dept_library'    => '📚 کتابخانه',
        'dept_technical'  => '🔧 فنی',
        'dept_other'      => '📋 سایر',
    ];

    if (isset($departments[$data])) {
        $this->stepDepartmentSelected($chatId, $baleId, $departments[$data]);
        return;
    }

    if (str_starts_with($data, 'req_page_')) {
        $page = (int) str_replace('req_page_', '', $data);
        $this->handleRequestPage($chatId, $baleId, $page);
        return;
    }

    if (str_starts_with($data, 'expert_phone_')) {
        // فقط اطلاع‌رسانی — شماره توی متن دکمه هست
        return;
    }
    match($data) {
        'for_self'        => $this->handleForSelf($chatId, $baleId),
        'for_other'       => $this->handleForOther($chatId, $baleId),
        'has_file'        => $this->handleHasFile($chatId, $baleId),
        'no_file'         => $this->handleNoFile($chatId, $baleId),
        'confirm_request' => $this->confirmRequest($chatId, $baleId),
        'cancel_request'  => $this->cancelRequest($chatId, $baleId),
        'back_to_menu'    => $this->handleBackToMenu($chatId, $baleId),
        default           => null,
    };
}

private function handleBackToMenu(int $chatId, int $baleId): void
{
    $this->bot->updateStep($baleId, 'main_menu', []);
    $this->bot->sendMainMenu($chatId);
}

private function handleRequestPage(int $chatId, int $baleId, int $page): void
{
    $session = $this->bot->getSession($baleId);
    $data    = $session->data ?? [];

    $requestIds = $data['request_ids'] ?? [];

    if (empty($requestIds) || !isset($requestIds[$page])) {
        $this->bot->sendMessage($chatId, '❌ خطا در نمایش درخواست.');
        return;
    }

    // آپدیت صفحه فعلی
    $data['request_page'] = $page;
    $this->bot->updateStep($baleId, 'viewing_requests', $data);

    $req = \App\Models\RequestModel::with(['department', 'files', 'assignedExpert'])
    ->find($requestIds[$page]);

    if (!$req) {
        $this->bot->sendMessage($chatId, '❌ درخواست یافت نشد.');
        return;
    }

    $this->showRequestPage($chatId, $baleId, $req, $page, count($requestIds));
}

private function stepDepartmentSelected(int $chatId, int $baleId, string $department): void
{
    $session = $this->bot->getSession($baleId);
    $data = $session->data ?? [];
    $data['department'] = $department;
    $this->bot->updateStep($baleId, 'register_body', $data);
    $this->bot->sendMessage($chatId, '📝 لطفاً متن درخواست خود را وارد کنید:');
}

private function handleHasFile(int $chatId, int $baleId): void
{
    $this->bot->sendMessage($chatId, '📎 لطفاً فایل خود را ارسال کنید:');
}

private function handleNoFile(int $chatId, int $baleId): void
{
    $session = $this->bot->getSession($baleId);
    $this->showConfirmation($chatId, $baleId, $session->data ?? []);
}

private function showConfirmation(int $chatId, int $baleId, array $data): void
{
    $this->deletePreviousBotMessage($chatId, $baleId);
    $isForOther = $data['is_for_other'] ?? false;
    $text  = "📋 خلاصه درخواست:\n\n";
    $text .= "👤 نوع: " . ($isForOther ? 'برای دیگران' : 'برای خودم') . "\n";
    if ($isForOther) {
        $text .= "📱 شماره موبایل: " . ($data['applicant_phone'] ?? '-') . "\n";
    }
    $text .= "🎓 شماره دانشجویی: " . ($data['applicant_student_number'] ?? '-') . "\n";
    $text .= "🏛 حوزه: " . ($data['department'] ?? '-') . "\n";
    $text .= "📝 متن درخواست: " . ($data['body'] ?? '-') . "\n";
    $text .= "📎 فایل پیوست: " . (isset($data['file_id']) ? 'دارد ✅' : 'ندارد') . "\n";

    $this->bot->updateStep($baleId, 'confirm', $data);

    $msgId = $this->bot->sendInlineMessage($chatId, $text, [ 
        [
            ['text' => '✅ تایید و ثبت', 'callback_data' => 'confirm_request'],
            ['text' => '❌ انصراف',       'callback_data' => 'cancel_request'],
        ]
    ]);
    $this->saveLastMessageId($baleId, $msgId);    
}

    private function handleForSelf(int $chatId, int $baleId): void
    {
        $this->bot->updateStep($baleId, 'register_student_number', [
            'is_for_other' => false,
        ]);
        $this->bot->sendMessage($chatId, '🎓 لطفاً شماره دانشجویی خود را وارد کنید:');
    }

    private function handleForOther(int $chatId, int $baleId): void
    {
        $this->bot->updateStep($baleId, 'register_other_phone', [
            'is_for_other' => true,
        ]);
        $this->bot->sendMessage($chatId, '📱 لطفاً شماره موبایل شخص مورد نظر را وارد کنید:');
    }
private function handleStart(int $chatId, int $baleId, array $from): void
{
    $user = $this->bot->findOrCreateUser(
        $baleId,
        trim(($from['first_name'] ?? '') . ' ' . ($from['last_name'] ?? '')),
        $from['username'] ?? null
    );

    // اگه شماره موبایل نداریم، بخواه
    if (!$user->phone) {
        $this->bot->updateStep($baleId, 'waiting_contact');
        $this->bot->requestContact($chatId);
        return;
    }

    // شماره داریم، منو نشون بده
    $this->bot->updateStep($baleId, 'main_menu');
    $this->bot->sendMessage($chatId, "سلام {$user->name}!\nبه " . config('app.name') . " خوش آمدید.");
    $this->bot->sendMainMenu($chatId);
}

private function handleRegister(int $chatId, int $baleId): void
{
    $this->deletePreviousBotMessage($chatId, $baleId);

    $msgId = $this->bot->sendInlineMessage($chatId, 'درخواست برای چه کسی است؟', [
        [
            ['text' => '👤 برای خودم',  'callback_data' => 'for_self'],
            ['text' => '👥 برای دیگران', 'callback_data' => 'for_other'],
        ]
    ]);

    $this->bot->updateStep($baleId, 'register_for_whom', ['last_message_id' => $msgId]);
}

private function handlePrevious(int $chatId, int $baleId): void
{
    $user = \App\Models\User::where('bale_id', $baleId)->first();

    if (!$user) {
        $this->bot->sendMessage($chatId, '❌ کاربر یافت نشد. لطفاً /start بزنید.');
        return;
    }

    $requests = \App\Models\RequestModel::where('requester_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();

    if ($requests->isEmpty()) {
        $this->bot->sendMessage($chatId, '📋 شما هنوز هیچ درخواستی ثبت نکرده‌اید.');
        $this->bot->sendMainMenu($chatId);
        return;
    }

    $requests = \App\Models\RequestModel::with(['department', 'files', 'assignedExpert'])
    ->where('requester_id', $user->id)
    ->orderBy('created_at', 'desc')
    ->get();
    
    // ذخیره لیست id ها توی session برای صفحه‌بندی
    $session = $this->bot->getSession($baleId);
    $data    = $session->data ?? [];
    $data['request_ids'] = $requests->pluck('id')->toArray();
    $data['request_page'] = 0;
    $this->bot->updateStep($baleId, 'viewing_requests', $data);

    $this->showRequestPage($chatId, $baleId, $requests->first(), 0, count($data['request_ids']));
}

private function showRequestPage(int $chatId, int $baleId, \App\Models\RequestModel $req, int $page, int $total): void
{
    $this->deletePreviousBotMessage($chatId, $baleId);

    $status = match($req->status) {
        'pending'  => '⏳ در انتظار بررسی',
        'seen'     => '👁 مشاهده شده',
        'answered' => '✅ پاسخ داده شده',
        'referred' => '↪️ ارجاع داده شده',
        default    => '❓ نامشخص',
    };

    $jalaliDate = \Morilog\Jalali\Jalalian::fromDateTime($req->created_at)
        ->format('Y/m/d H:i');

    $dept      = $req->department?->name ?? 'نامشخص';
    $forOther  = $req->is_for_other ? '👥 برای دیگری' : '👤 برای خودم';
    $hasFile   = $req->files->isNotEmpty() ? '📎 دارد' : '➖ ندارد';
    $studentNo = $req->applicant_student_number ?? '---';

    // اطلاعات کارشناس
    $expert     = $req->assignedExpert;
    $expertName = $expert?->name ?? 'هنوز تخصیص نیافته';
    $expertPhone = $expert?->phone ?? null;

    $text  = "📋 درخواست " . ($page + 1) . " از {$total}\n";
    $text .= "➖➖➖➖➖➖➖➖\n";
    $text .= "🔖 کد پیگیری: {$req->tracking_code}\n";
    $text .= "👤 نوع درخواست: {$forOther}\n";
    $text .= "🎓 شماره دانشجویی: {$studentNo}\n";
    $text .= "🏛 حوزه: {$dept}\n";
    $text .= "📊 وضعیت: {$status}\n";
    $text .= "👨‍💼 کارشناس: {$expertName}\n";
    $text .= "📎 فایل پیوست: {$hasFile}\n";
    $text .= "📅 تاریخ ثبت: {$jalaliDate}\n";
    $text .= "➖➖➖➖➖➖➖➖\n";
    $text .= "📝 متن درخواست:\n{$req->body}";

    // دکمه‌های صفحه‌بندی
    $buttons = [];
    $nav     = [];

    if ($page > 0) {
        $nav[] = ['text' => '▶️ قبلی', 'callback_data' => 'req_page_' . ($page - 1)];
    }

    if ($page < $total - 1) {
        $nav[] = ['text' => 'بعدی ◀️', 'callback_data' => 'req_page_' . ($page + 1)];
    }

    if (!empty($nav)) {
        $buttons[] = $nav;
    }

    // دکمه تماس با کارشناس — فقط اگه کارشناس assign شده و شماره داره
    if ($expert && $expertPhone && $req->status !== 'pending') {
        $buttons[] = [
            ['text' => '📞 تماس با کارشناس: ' . $expertPhone, 'callback_data' => 'expert_phone_' . $req->id]
        ];
    }

    $buttons[] = [['text' => '🏠 بازگشت به منو', 'callback_data' => 'back_to_menu']];

    $msgId = $this->bot->sendInlineMessage($chatId, $text, $buttons);
    $this->saveLastMessageId($baleId, $msgId);
}

    private function handleContact(int $chatId): void
    {
        $this->bot->sendInlineMessage($chatId, 
        "📞 اطلاعات تماس دانشگاه:
\n  - تلفن: 57202000-051
\n  - ایمیل: info@gonabad.ac.ir",[
            [['text' => '🔗 باز کردن 118 دانشگاه', 'url' => 'https://gonabad.ac.ir/118']]
        ]);
    }

    private function handleWebsite(int $chatId): void
    {
        $this->bot->sendInlineMessage($chatId, '🌐 ورود به وب سایت دانشگاه:', [
            [['text' => '🔗 باز کردن وب سایت', 'url' => 'https://gonabad.ac.ir']]
        ]);
    }

   private function handleStats(int $chatId, int $baleId): void
{
    $from = now()->subMonth();

    $stats = \App\Models\RequestModel::where('created_at', '>=', $from)
        ->selectRaw('status, COUNT(*) as count')
        ->groupBy('status')
        ->pluck('count', 'status');

    $pending  = $stats['pending']  ?? 0;
    $seen     = $stats['seen']     ?? 0;
    $answered = $stats['answered'] ?? 0;
    $referred = $stats['referred'] ?? 0;
    $total    = $pending + $seen + $answered + $referred;

    // تعداد کاربران فعال در یک ماه اخیر
    $activeUsers = \App\Models\RequestModel::where('created_at', '>=', $from)
        ->distinct('requester_id')
        ->count('requester_id');

    $jalaliFrom = \Morilog\Jalali\Jalalian::fromDateTime($from)->format('Y/m/d');
    $jalaliTo   = \Morilog\Jalali\Jalalian::fromDateTime(now())->format('Y/m/d');

    $text  = "📊 آمار درخواست‌های بازو دانشگاه گناباد\n";
    $text .= "📅 از {$jalaliFrom} تا {$jalaliTo}\n";
    $text .= "➖➖➖➖➖➖➖➖\n";
    $text .= "⏳ در انتظار بررسی:   {$pending}\n";
    $text .= "👁 مشاهده شده:        {$seen}\n";
    $text .= "✅ پاسخ داده شده:     {$answered}\n";
    $text .= "↪️ ارجاع داده شده:    {$referred}\n";
    $text .= "➖➖➖➖➖➖➖➖\n";
    $text .= "📨 مجموع کل درخواست‌ها: {$total}\n";
    $text .= "👥 تعداد دانشجویان:     {$activeUsers}\n";

    $this->bot->sendMessage($chatId, $text);
    $this->bot->sendMainMenu($chatId);
}

    private function handleStep(int $chatId, int $baleId, string $text, $session): void
    {
        match($session->step) {
            'register_other_phone'    => $this->stepOtherPhone($chatId, $baleId, $text, $session),
            'register_other_student'  => $this->stepOtherStudent($chatId, $baleId, $text, $session),
            'register_student_number' => $this->stepStudentNumber($chatId, $baleId, $text, $session),
            'register_department'     => $this->stepDepartment($chatId, $baleId, $text, $session),
            'register_body'           => $this->stepBody($chatId, $baleId, $text, $session),
            'register_file'           => $this->stepFile($chatId, $baleId, $text, $session),
            default                   => $this->bot->sendMainMenu($chatId),
        };
    }

    // شماره موبایل نفر دیگه
private function stepOtherPhone(int $chatId, int $baleId, string $text, $session): void
{
    $phone = NumberHelper::onlyDigits($text);
    
    // اگه با 98 شروع شده تبدیل کن
    if (str_starts_with($phone, '98')) {
        $phone = '0' . substr($phone, 2);
    }

    if (!preg_match('/^09[0-9]{9}$/', $phone)) {
        $this->bot->sendMessage($chatId, '❌ شماره موبایل معتبر نیست. لطفاً دوباره وارد کنید (مثال: 09121234567):');
        return;
    }

    $data = $session->data ?? [];
    $data['applicant_phone'] = $phone;
    $this->bot->updateStep($baleId, 'register_other_student', $data);
    $this->bot->sendMessage($chatId, '🎓 لطفاً شماره دانشجویی شخص مورد نظر را وارد کنید:');
}

    // شماره دانشجویی نفر دیگه
private function stepOtherStudent(int $chatId, int $baleId, string $text, $session): void
{
    $studentNumber = NumberHelper::onlyDigits($text);

    if (empty($studentNumber)) {
        $this->bot->sendMessage($chatId, '❌ شماره دانشجویی باید عددی باشد. لطفاً دوباره وارد کنید:');
        return;
    }

    $data = $session->data ?? [];
    $data['applicant_student_number'] = $studentNumber;
    $this->bot->updateStep($baleId, 'register_department', $data);
    $this->sendDepartmentMenu($chatId);
}

    // شماره دانشجویی خودش
private function stepStudentNumber(int $chatId, int $baleId, string $text, $session): void
{
    $studentNumber = NumberHelper::onlyDigits($text);

    if (empty($studentNumber)) {
        $this->bot->sendMessage($chatId, '❌ شماره دانشجویی باید عددی باشد. لطفاً دوباره وارد کنید:');
        return;
    }

    $data = $session->data ?? [];
    $data['applicant_student_number'] = $studentNumber;
    $this->bot->updateStep($baleId, 'register_department', $data);
    $this->sendDepartmentMenu($chatId);
}

    // انتخاب حوزه
private function stepDepartment(int $chatId, int $baleId, string $text, $session): void
{
    // این مرحله حالا از طریق inline button هندل میشه
    $this->sendDepartmentMenu($chatId);
}

    // متن درخواست
    private function stepBody(int $chatId, int $baleId, string $text, $session): void
    {
        $data = $session->data ?? [];
        $data['body'] = $text;
        $this->bot->updateStep($baleId, 'register_file', $data);
        $this->bot->sendInlineMessage($chatId, '📎 آیا فایل پیوستی دارید؟', [
            [
                ['text' => '📎 بله، فایل دارم', 'callback_data' => 'has_file'],
                ['text' => '✅ خیر، ادامه بده', 'callback_data' => 'no_file'],
            ]
        ]);
    }

    // مرحله فایل
    private function stepFile(int $chatId, int $baleId, string $text, $session): void
    {
        // اگه کاربر متن فرستاد به جای فایل
        $this->bot->sendMessage($chatId, '❌ لطفاً یک فایل ارسال کنید یا گزینه «خیر» را انتخاب کنید.');
    }

    // نمایش منوی حوزه‌ها
private function sendDepartmentMenu(int $chatId): void
{
    $this->bot->sendInlineMessage($chatId, '🏛 لطفاً حوزه مربوطه را انتخاب کنید:', [
        [
            ['text' => '🎓 آموزش',    'callback_data' => 'dept_education'],
            ['text' => '💰 مالی',      'callback_data' => 'dept_finance'],
        ],
        [
            ['text' => '🏠 خوابگاه',  'callback_data' => 'dept_dormitory'],
            ['text' => '📚 کتابخانه', 'callback_data' => 'dept_library'],
        ],
        [
            ['text' => '🔧 فنی',      'callback_data' => 'dept_technical'],
            ['text' => '📋 سایر',     'callback_data' => 'dept_other'],
        ],
    ]);
}

    private function stepFileReceived(int $chatId, int $baleId, array $file, $session): void
{
    // اگه photo بود، آخرین (بزرگترین) سایز رو بگیر
    $fileId = is_array($file) && isset($file[0])
        ? end($file)['file_id']
        : $file['file_id'];

    $data = $session->data ?? [];
    $data['file_id'] = $fileId;

    $this->showConfirmation($chatId, $baleId, $data);
}
private function handleContact_received(int $chatId, int $baleId, array $contact, array $from): void
{
    // فقط contact خود کاربر رو قبول کن
    if (($contact['user_id'] ?? null) != $baleId) {
        $this->bot->sendMessage($chatId, '❌ لطفاً فقط شماره موبایل خودتان را به اشتراک بگذارید.');
        return;
    }

    $phone = $contact['phone_number'];
    // اگه بدون 0 بود اضافه کن
    if (!str_starts_with($phone, '0')) {
        $phone = '0' . ltrim($phone, '+98');
    }

    \App\Models\User::where('bale_id', $baleId)->update(['phone' => $phone]);

    $this->bot->updateStep($baleId, 'main_menu');
    $this->bot->sendMessage($chatId, "✅ شماره موبایل شما ثبت شد.\nبه بازو پشتیبانی دانشگاه گناباد خوش آمدید.");
    $this->bot->sendMainMenu($chatId);
}
private function confirmRequest(int $chatId, int $baleId): void
{
    $session = $this->bot->getSession($baleId);
    $data    = $session->data ?? [];

    // ساخت کد پیگیری
    $trackingCode = strtoupper(substr(md5(uniqid()), 0, 8));

    // ذخیره درخواست توی دیتابیس
    $user = \App\Models\User::where('bale_id', $baleId)->first();

    $request = \App\Models\RequestModel::create([
        'tracking_code'            => $trackingCode,
        'requester_id'             => $user->id,
        'applicant_phone'          => $data['applicant_phone'] ?? $user->phone,
        'applicant_student_number' => $data['applicant_student_number'] ?? null,
        'department_id'            => $this->getDepartmentId($data['department'] ?? ''),
        'body'                     => $data['body'] ?? '',
        'is_for_other'             => $data['is_for_other'] ?? false,
        'status'                   => 'pending',
    ]);

    // اگه فایل داشت ذخیره کن
    if (!empty($data['file_id'])) {
        \App\Models\RequestFile::create([
            'request_id' => $request->id,
            'file_path'  => $data['file_id'],
            'file_name'  => $data['file_id'],
            'file_type'  => 'bale_file',
            'file_size'  => 0,
        ]);
    }

    // پاک کردن session
    $this->bot->updateStep($baleId, 'main_menu', []);

    $this->bot->sendMessage($chatId,
        "✅ درخواست شما با موفقیت ثبت شد.\n\n" .
        "🔖 کد پیگیری: <code>{$trackingCode}</code>\n\n" .
        "با استفاده از این کد می‌توانید وضعیت درخواست خود را پیگیری کنید."
    );
    $this->bot->sendMainMenu($chatId);
}

private function cancelRequest(int $chatId, int $baleId): void
{
    $this->bot->updateStep($baleId, 'main_menu', []);
    $this->bot->sendMessage($chatId, '❌ درخواست لغو شد.');
    $this->bot->sendMainMenu($chatId);
}

private function getDepartmentId(string $departmentName): ?int
{
    // اموجی و فاصله رو حذف کن
    $clean = trim(preg_replace('/[\x{1F000}-\x{1FFFF}]/u', '', $departmentName));
    $clean = trim($clean);
    
    $dept = \App\Models\Department::where('name', 'like', '%' . $clean . '%')
                                   ->where('is_active', true)
                                   ->first();
    return $dept?->id;
}
private function deletePreviousBotMessage(int $chatId, int $baleId): void
{
    $session = $this->bot->getSession($baleId);
    $data    = $session->data ?? [];

    if (!empty($data['last_message_id'])) {
        $this->bot->deleteMessage($chatId, $data['last_message_id']);
    }
}

private function saveLastMessageId(int $baleId, ?int $messageId): void
{
    if (!$messageId) return;

    $session = $this->bot->getSession($baleId);
    $data    = $session->data ?? [];
    $data['last_message_id'] = $messageId;
    $this->bot->updateStep($baleId, $session->step, $data);
}

private function restoreCurrentStep(int $chatId, int $baleId, $session): void
{
    match($session->step) {
        'register_for_whom'    => $this->handleRegister($chatId, $baleId),
        'register_other_phone' => $this->bot->sendMessage($chatId, '📱 لطفاً شماره موبایل شخص مورد نظر را وارد کنید:'),
        'register_other_student',
        'register_student_number' => $this->bot->sendMessage($chatId, '🎓 لطفاً شماره دانشجویی را وارد کنید:'),
        'register_department'  => $this->sendDepartmentMenu($chatId),
        'register_body'        => $this->bot->sendMessage($chatId, '📝 لطفاً متن درخواست خود را وارد کنید:'),
        'register_file'        => $this->bot->sendInlineMessage($chatId, '📎 آیا فایل پیوستی دارید؟', [
            [
                ['text' => '📎 بله، فایل دارم', 'callback_data' => 'has_file'],
                ['text' => '✅ خیر، ادامه بده',  'callback_data' => 'no_file'],
            ]
        ]),
        'confirm'              => $this->showConfirmation($chatId, $baleId, $session->data ?? []),
        'viewing_requests'     => $this->handlePrevious($chatId, $baleId),
        default                => $this->bot->sendMainMenu($chatId),
    };
}
}