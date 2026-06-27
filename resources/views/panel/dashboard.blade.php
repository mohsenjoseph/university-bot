@extends('layouts.panel')

@section('title', 'داشبورد')

@section('content')

    <!-- آمار -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</div>
            <div class="text-sm text-gray-500 mt-1">⏳ در انتظار بررسی</div>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-blue-600">{{ $stats['seen'] }}</div>
            <div class="text-sm text-gray-500 mt-1">👁 مشاهده شده</div>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-green-600">{{ $stats['answered'] }}</div>
            <div class="text-sm text-gray-500 mt-1">✅ پاسخ داده شده</div>
        </div>
        <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-purple-600">{{ $stats['referred'] }}</div>
            <div class="text-sm text-gray-500 mt-1">↪️ ارجاع داده شده</div>
        </div>
    </div>

    <!-- ۵ درخواست اخیر -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 border-b flex justify-between items-center">
            <h2 class="font-bold text-gray-700 text-sm">🆕 ۵ درخواست اخیر</h2>
            <a href="{{ route('panel.requests.index') }}" class="text-blue-600 hover:underline text-xs">مشاهده همه ←</a>
        </div>
        <!-- جدول دسکتاپ -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-right">کد پیگیری</th>
                        <th class="px-4 py-3 text-right">درخواست‌دهنده</th>
                        <th class="px-4 py-3 text-right">حوزه</th>
                        <th class="px-4 py-3 text-right">وضعیت</th>
                        <th class="px-4 py-3 text-right">تاریخ ثبت</th>
                        <th class="px-4 py-3 text-right">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentRequests as $req)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono">{{ $req->tracking_code }}</td>
                            <td class="px-4 py-3">{{ $req->requester?->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $req->department?->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @include('panel.partials.status-badge', ['status' => $req->status])
                            </td>
                            <td class="px-4 py-3 text-gray-500">
                                {{ \Morilog\Jalali\Jalalian::fromDateTime($req->created_at)->format('Y/m/d H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('panel.requests.show', $req->id) }}" class="text-blue-600 hover:underline text-xs">مشاهده</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">هیچ درخواستی یافت نشد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- کارت موبایل -->
        <div class="md:hidden divide-y">
            @forelse($recentRequests as $req)
                <div class="p-4 space-y-2 text-sm">
                    <div class="flex justify-between items-start">
                        <span class="font-mono font-bold text-gray-700">{{ $req->tracking_code }}</span>
                        @include('panel.partials.status-badge', ['status' => $req->status])
                    </div>
                    <div class="text-gray-500">👤 {{ $req->requester?->name ?? '-' }}</div>
                    <div class="text-gray-500">🏛 {{ $req->department?->name ?? '-' }}</div>
                    <a href="{{ route('panel.requests.show', $req->id) }}"
                       class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg text-sm hover:bg-blue-700 transition mt-2">
                        مشاهده درخواست
                    </a>
                </div>
            @empty
                <div class="p-8 text-center text-gray-400 text-sm">هیچ درخواستی یافت نشد</div>
            @endforelse
        </div>
    </div>

@endsection