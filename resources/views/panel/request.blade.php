@extends('layouts.panel')

@section('title', 'جزئیات درخواست ' . $request->tracking_code)

@section('breadcrumb')
    <span class="text-gray-400">/</span>
    <span class="text-gray-600">درخواست {{ $request->tracking_code }}</span>
@endsection

@section('content')

@php
    $lastReferral = $request->replies->where('is_referral', true)->last();
    $isReferrer   = $lastReferral && $lastReferral->expert_id === $user->id;
    $isReceiver   = $request->status === 'referred' && (int)$request->assigned_expert_id === (int)$user->id;
    $canReply     = $request->status !== 'answered';
    $canRefer     = $request->status !== 'answered';
    $hasHistory   = $request->replies->isNotEmpty();

    $defaultTab = $hasHistory ? 'history' : ($canReply ? 'reply' : ($canRefer ? 'refer' : 'return'));
@endphp

<div class="max-w-5xl mx-auto" x-data="{ tab: '{{ $defaultTab }}' }">

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm mb-4">
            ✅ {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <!-- ستون راست: اطلاعات پایه (ثابت) -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-bold text-gray-700 text-sm">📋 {{ $request->tracking_code }}</h3>
                @switch($request->status)
                    @case('pending')
                        <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs">⏳ در انتظار</span>
                        @break
                    @case('seen')
                        <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-xs">👁 مشاهده شده</span>
                        @break
                    @case('answered')
                        <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs">✅ پاسخ داده شده</span>
                        @break
                    @case('referred')
                        <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded-full text-xs">↪️ ارجاع شده</span>
                        @break
                @endswitch
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm border-t pt-3">
                <div class="flex gap-2"><span class="text-gray-400 shrink-0">درخواست‌دهنده:</span><span>{{ $request->requester?->name ?? '-' }}</span></div>
                <div class="flex gap-2"><span class="text-gray-400 shrink-0">موبایل:</span><span class="font-mono">{{ $request->applicant_phone ?? '-' }}</span></div>
                <div class="flex gap-2"><span class="text-gray-400 shrink-0">شماره دانشجویی:</span><span class="font-mono">{{ $request->applicant_student_number ?? '-' }}</span></div>
                <div class="flex gap-2"><span class="text-gray-400 shrink-0">حوزه:</span><span>{{ $request->department?->name ?? '-' }}</span></div>
                <div class="flex gap-2"><span class="text-gray-400 shrink-0">نوع:</span><span>{{ $request->is_for_other ? '👥 برای دیگری' : '👤 برای خودم' }}</span></div>
                <div class="flex gap-2"><span class="text-gray-400 shrink-0">کارشناس:</span><span>{{ $request->assignedExpert?->name ?? 'تخصیص نیافته' }}</span></div>
                <div class="flex gap-2 sm:col-span-2"><span class="text-gray-400 shrink-0">تاریخ ثبت:</span><span>{{ \Morilog\Jalali\Jalalian::fromDateTime($request->created_at)->format('Y/m/d H:i') }}</span></div>
            </div>

            <div class="mt-3 pt-3 border-t">
                <span class="text-gray-400 text-sm">متن درخواست:</span>
                <div class="mt-2 bg-gray-50 rounded-lg p-3 text-sm leading-7">{{ $request->body }}</div>
            </div>

            @if($request->files->isNotEmpty())
                <div class="mt-3 pt-3 border-t">
                    <span class="text-gray-400 text-sm">فایل پیوست:</span>
                    <div class="mt-2 space-y-2">
                        @foreach($request->files as $file)
                            <div class="flex items-center gap-2 text-sm bg-gray-50 rounded p-2">
                                <span>📎</span><span class="text-gray-600">{{ $file->file_name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- ستون چپ: تب‌ها -->
        <div class="bg-white rounded-xl shadow-sm p-4">

            <!-- نوار تب -->
            <div class="flex border-b mb-4 -mx-4 px-4 overflow-x-auto">
                @if($hasHistory)
                    <button @click="tab = 'history'"
                            :class="tab === 'history' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-gray-500'"
                            class="px-3 py-2 text-sm border-b-2 whitespace-nowrap transition">
                        🕓 سابقه پاسخ‌ها
                    </button>
                @endif
                @if($canReply)
                    <button @click="tab = 'reply'"
                            :class="tab === 'reply' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-gray-500'"
                            class="px-3 py-2 text-sm border-b-2 whitespace-nowrap transition">
                        ✏️ ارسال پاسخ
                    </button>
                @endif
                @if($canRefer)
                    <button @click="tab = 'refer'"
                            :class="tab === 'refer' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-gray-500'"
                            class="px-3 py-2 text-sm border-b-2 whitespace-nowrap transition">
                        ↪️ ارجاع
                    </button>
                @endif
                @if($isReferrer || $isReceiver)
                    <button @click="tab = 'return'"
                            :class="tab === 'return' ? 'border-blue-600 text-blue-600 font-bold' : 'border-transparent text-gray-500'"
                            class="px-3 py-2 text-sm border-b-2 whitespace-nowrap transition">
                        ⏪ عودت/بازگشت
                    </button>
                @endif
            </div>

            <!-- تب: سابقه پاسخ‌ها -->
            @if($hasHistory)
                <div x-show="tab === 'history'" x-cloak class="space-y-2">
                    @foreach($request->replies as $reply)
                        <div class="border rounded-lg p-3 text-sm space-y-1
                            {{ $reply->is_referral ? 'border-purple-200 bg-purple-50' : 'border-green-200 bg-green-50' }}">
                            <div class="flex flex-wrap justify-between gap-2">
                                <span class="font-bold text-xs">
                                    {{ $reply->is_referral ? '↪️ ارجاع' : '✅ پاسخ' }} توسط {{ $reply->expert?->name ?? '-' }}
                                </span>
                                <span class="text-gray-400 text-xs">
                                    {{ \Morilog\Jalali\Jalalian::fromDateTime($reply->created_at)->format('Y/m/d H:i') }}
                                </span>
                            </div>
                            @if($reply->is_referral)
                                <div class="text-purple-700 text-xs">ارجاع به: {{ $reply->referredTo?->name ?? '-' }}</div>
                            @endif
                            @if($reply->body)
                                <div class="leading-6">{{ $reply->body }}</div>
                            @endif
                            @if($reply->file_name)
                                <div class="flex items-center gap-2 bg-white rounded p-2">
                                    <span>📎</span>
                                    <a href="{{ Storage::url($reply->file_path) }}" class="text-blue-600 hover:underline text-xs">{{ $reply->file_name }}</a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- تب: ارسال پاسخ -->
            @if($canReply)
                <div x-show="tab === 'reply'" x-cloak>
                    <form method="POST"
                          action="{{ route('panel.requests.reply', $request->id) }}"
                          enctype="multipart/form-data"
                          class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">متن پاسخ</label>
                            <textarea name="body" rows="6" required
                                      class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">فایل پیوست (اختیاری)</label>
                            <input type="file" name="file" class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <button type="submit"
                                class="w-full bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition text-sm">
                            ✅ ارسال پاسخ
                        </button>
                    </form>
                </div>
            @endif

            <!-- تب: ارجاع -->
            @if($canRefer)
                <div x-show="tab === 'refer'" x-cloak>
                    <form method="POST"
                          action="{{ route('panel.requests.refer', $request->id) }}"
                          class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">انتخاب کارشناس</label>
                            <select name="expert_id" required
                                    class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <option value="">-- انتخاب کنید --</option>
                                @foreach($experts as $expert)
                                    <option value="{{ $expert->id }}">{{ $expert->name }} — {{ $expert->department?->name ?? 'بدون حوزه' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">توضیحات ارجاع (اختیاری)</label>
                            <textarea name="note" rows="3"
                                      class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"></textarea>
                        </div>
                        <button type="submit"
                                class="w-full bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition text-sm">
                            ↪️ ارجاع درخواست
                        </button>
                    </form>
                </div>
            @endif

            <!-- تب: عودت/بازگشت -->
            @if($isReferrer || $isReceiver)
                <div x-show="tab === 'return'" x-cloak class="space-y-3">
                    @if($isReferrer)
                        <div class="border border-orange-200 bg-orange-50 rounded-lg p-3">
                            <p class="text-sm text-orange-700 mb-2">شما این درخواست را ارجاع داده‌اید. می‌توانید ارجاع را لغو کرده و درخواست را نزد خود نگه دارید.</p>
                            <form method="POST" action="{{ route('panel.requests.recall', $request->id) }}">
                                @csrf
                                <button type="submit" class="w-full bg-orange-500 text-white px-6 py-2 rounded-lg hover:bg-orange-600 transition text-sm">
                                    ⏪ بازگشت ارجاع
                                </button>
                            </form>
                        </div>
                    @endif

                    @if($isReceiver)
                        <div class="border border-gray-200 bg-gray-50 rounded-lg p-3">
                            <p class="text-sm text-gray-600 mb-2">این درخواست به شما ارجاع شده. در صورت نیاز می‌توانید آن را به ارجاع‌دهنده عودت دهید.</p>
                            <form method="POST" action="{{ route('panel.requests.return', $request->id) }}">
                                @csrf
                                <button type="submit" class="w-full bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition text-sm">
                                    ↩️ عودت به ارجاع‌دهنده
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif

        </div>

    </div>
</div>

@endsection