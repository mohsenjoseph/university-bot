<?php

namespace App\Services;

use App\Models\BotSession;
use Illuminate\Support\Facades\Http;

class BotService
{
    private string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = config('services.bale.api_url') . config('services.bale.token');
    }

    // گرفتن یا ساختن session کاربر
    public function getSession(int $baleId): BotSession
    {
        return BotSession::firstOrCreate(
            ['bale_id' => $baleId],
            ['step' => 'main_menu', 'data' => []]
        );
    }

    // آپدیت کردن step
    public function updateStep(int $baleId, string $step, array $data = []): void
    {
        BotSession::where('bale_id', $baleId)->update([
            'step' => $step,
            'data' => $data,
        ]);
    }

  public function sendMessage(int $chatId, string $text, array $keyboard = []): ?int
{
    $payload = [
        'chat_id' => $chatId,
        'text'    => $text,
    ];

    if (!empty($keyboard)) {
        $payload['reply_markup'] = json_encode([
            'keyboard'        => $keyboard,
            'resize_keyboard' => true,
        ]);
    }

    $response = Http::post("{$this->apiUrl}/sendMessage", $payload);
    return $response->json('result.message_id');
}

public function sendInlineMessage(int $chatId, string $text, array $buttons): ?int
{
    $response = Http::post("{$this->apiUrl}/sendMessage", [
        'chat_id'      => $chatId,
        'text'         => $text,
        'reply_markup' => json_encode([
            'inline_keyboard' => $buttons,
        ]),
    ]);

    return $response->json('result.message_id');
}

    // منوی اصلی
public function sendMainMenu(int $chatId): ?int
{
    return $this->sendMessage($chatId, 'یک گزینه را انتخاب کنید:', [
        [
            ['text' => '📝 ثبت درخواست'],
            ['text' => '📋 درخواست‌های قبلی'],
        ],
        [
            ['text' => '📞 اطلاعات تماس'],
            ['text' => '🌐 ورود به وب سایت'],
        ],
        [
            ['text' => '📊 آمار و اطلاعات'],
        ],
    ]);
}

    public function requestContact(int $chatId): void
{
    Http::post("{$this->apiUrl}/sendMessage", [
        'chat_id' => $chatId,
        'text' => "👋 سلام!\nبه " . config('app.name') . " خوش آمدید.\n\n📱 برای استفاده از ربات، لطفاً شماره موبایل خود را به اشتراک بگذارید:",
        'reply_markup' => json_encode([
            'keyboard' => [
                [
                    [
                        'text'            => '📱 اشتراک‌گذاری شماره موبایل',
                        'request_contact' => true,
                    ]
                ]
            ],
            'resize_keyboard'   => true,
            'one_time_keyboard' => true,
        ]),
    ]);
}

public function findOrCreateUser(int $baleId, string $name, ?string $username): \App\Models\User
{
    return \App\Models\User::firstOrCreate(
        ['bale_id' => $baleId],
        [
            'name'         => $name,
            'email'         => $baleId . '@bale.temp',            
            'password'      => bcrypt(str()->random(32)),
            'bale_username'=> $username,
            'role'         => 'student',
        ]
    );
}

public function deleteMessage(int $chatId, int $messageId): void
{
    Http::post("{$this->apiUrl}/deleteMessage", [
        'chat_id'    => $chatId,
        'message_id' => $messageId,
    ]);
}
}