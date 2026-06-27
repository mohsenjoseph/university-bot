@extends('layouts.panel')

@section('title', 'گزارشات')

@section('content')

    <!-- فیلتر بازه زمانی -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('panel.reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">از تاریخ</label>
                <input type="date" name="from_date" value="{{ $fromDate }}"
                    class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">تا تاریخ</label>
                <input type="date" name="to_date" value="{{ $toDate }}"
                    class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="lg:col-span-2">
                <button type="submit" class="w-full sm:w-auto bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition text-sm">
                    🔍 اعمال بازه زمانی
                </button>
            </div>
        </form>
    </div>

    <!-- گزارش کارشناسان -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="p-4 border-b">
            <h2 class="font-bold text-gray-700 text-sm">👨‍💼 گزارش کارشناسان</h2>
        </div>

        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm ">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-right w-[25%]">نام کارشناس</th>
                        <th class="px-4 py-3 text-center w-[13%]">⏳ در انتظار</th>
                        <th class="px-4 py-3 text-center w-[13%]">👁 مشاهده شده</th>
                        <th class="px-4 py-3 text-center w-[13%]">✅ پاسخ داده شده</th>
                        <th class="px-4 py-3 text-center w-[13%]">↪️ ارجاع شده</th>
                        <th class="px-4 py-3 text-center w-[13%]">📨 مجموع</th>
                        <th class="px-4 py-3 text-center w-[10%]">عملیات</th>                        
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($expertReports as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $row['name'] }}</td>
                            <td class="px-4 py-3 text-center">{{ $row['pending'] }}</td>
                            <td class="px-4 py-3 text-center">{{ $row['seen'] }}</td>
                            <td class="px-4 py-3 text-center">{{ $row['answered'] }}</td>
                            <td class="px-4 py-3 text-center">{{ $row['referred'] }}</td>
                            <td class="px-4 py-3 text-center font-bold">{{ $row['total'] }}</td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('panel.requests.index', ['expert_id' => $row['expert_id'], 'from_date' => $row['from_date'], 'to_date' => $row['to_date']]) }}"
                                        class="text-blue-600 hover:underline text-xs">مشاهده درخواست‌ها</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">داده‌ای یافت نشد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- کارت موبایل -->
        <div class="md:hidden divide-y">
            @forelse($expertReports as $row)
                <div class="p-4 space-y-2 text-sm">
                    <div class="font-bold text-gray-700">{{ $row['name'] }}</div>
                    <div class="grid grid-cols-2 gap-2 text-gray-500">
                        <div>⏳ در انتظار: {{ $row['pending'] }}</div>
                        <div>👁 مشاهده شده: {{ $row['seen'] }}</div>
                        <div>✅ پاسخ داده شده: {{ $row['answered'] }}</div>
                        <div>↪️ ارجاع شده: {{ $row['referred'] }}</div>
                    </div>
                    <div class="font-bold">📨 مجموع: {{ $row['total'] }}</div>
                    <a href="{{ route('panel.requests.index', ['expert_id' => $row['expert_id'], 'from_date' => $row['from_date'], 'to_date' => $row['to_date']]) }}"
                        class="block text-center bg-blue-600 text-white py-2 rounded-lg text-sm hover:bg-blue-700 transition mt-2">
                            مشاهده درخواست‌ها
                        </a>
                    
                </div>
            @empty
                <div class="p-8 text-center text-gray-400 text-sm">داده‌ای یافت نشد</div>
            @endforelse
        </div>
    </div>

    <!-- گزارش حوزه‌ها — فقط برای ادمین -->
    @if($user->role === 'admin')
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b">
                <h2 class="font-bold text-gray-700 text-sm">🏛 گزارش حوزه‌ها</h2>
            </div>

            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm table-fixed">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-right w-[25%]">نام حوزه</th>
                            <th class="px-4 py-3 text-center w-[15%]">⏳ در انتظار</th>
                            <th class="px-4 py-3 text-center w-[15%]">👁 مشاهده شده</th>
                            <th class="px-4 py-3 text-center w-[15%]">✅ پاسخ داده شده</th>
                            <th class="px-4 py-3 text-center w-[15%]">↪️ ارجاع شده</th>
                            <th class="px-4 py-3 text-center w-[15%]">📨 مجموع</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($departmentReports as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">{{ $row['name'] }}</td>
                                <td class="px-4 py-3 text-center">{{ $row['pending'] }}</td>
                                <td class="px-4 py-3 text-center">{{ $row['seen'] }}</td>
                                <td class="px-4 py-3 text-center">{{ $row['answered'] }}</td>
                                <td class="px-4 py-3 text-center">{{ $row['referred'] }}</td>
                                <td class="px-4 py-3 text-center font-bold">{{ $row['total'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">داده‌ای یافت نشد</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="md:hidden divide-y">
                @forelse($departmentReports as $row)
                    <div class="p-4 space-y-2 text-sm">
                        <div class="font-bold text-gray-700">{{ $row['name'] }}</div>
                        <div class="grid grid-cols-2 gap-2 text-gray-500">
                            <div>⏳ در انتظار: {{ $row['pending'] }}</div>
                            <div>👁 مشاهده شده: {{ $row['seen'] }}</div>
                            <div>✅ پاسخ داده شده: {{ $row['answered'] }}</div>
                            <div>↪️ ارجاع شده: {{ $row['referred'] }}</div>
                        </div>
                        <div class="font-bold">📨 مجموع: {{ $row['total'] }}</div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-400 text-sm">داده‌ای یافت نشد</div>
                @endforelse
            </div>
        </div>
    @endif

@endsection