@extends('layouts.panel')

@section('title', 'درخواست‌ها')

@section('content')
    <!-- فیلترها -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('panel.requests.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs text-gray-500 mb-1">کد پیگیری</label>
                <input type="text" name="tracking_code" value="{{ request('tracking_code') }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">وضعیت</label>
                <select name="status" class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="">همه</option>
                    <option value="pending"  {{ request('status') == 'pending'  ? 'selected' : '' }}>⏳ در انتظار</option>
                    <option value="seen"     {{ request('status') == 'seen'     ? 'selected' : '' }}>👁 مشاهده شده</option>
                    <option value="answered" {{ request('status') == 'answered' ? 'selected' : '' }}>✅ پاسخ داده شده</option>
                    <option value="referred" {{ request('status') == 'referred' ? 'selected' : '' }}>↪️ ارجاع شده</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">حوزه</label>
                <select name="department_id" class="w-full border rounded-lg px-3 py-2 text-sm">
                    <option value="">همه</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">از تاریخ</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">تا تاریخ</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}"
                       class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="sm:col-span-2 lg:col-span-5 flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition text-sm">
                    🔍 اعمال فیلتر
                </button>
                <a href="{{ route('panel.requests.index') }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg hover:bg-gray-200 transition text-sm">
                    حذف فیلتر
                </a>
            </div>
        </form>
    </div>

  <!-- جدول -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm table-fixed">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-right w-[12%]">کد پیگیری</th>
                    <th class="px-4 py-3 text-right w-[16%]">درخواست‌دهنده</th>
                    <th class="px-4 py-3 text-right w-[12%]">حوزه</th>
                    <th class="px-4 py-3 text-right w-[15%]">کارشناس</th>
                    <th class="px-4 py-3 text-right w-[15%]">وضعیت</th>
                    <th class="px-4 py-3 text-right w-[18%]">تاریخ ثبت</th>
                    <th class="px-4 py-3 text-right w-[12%]">عملیات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($requests as $req)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono truncate">{{ $req->tracking_code }}</td>
                        <td class="px-4 py-3 truncate">{{ $req->requester?->name ?? '-' }}</td>
                        <td class="px-4 py-3 truncate">{{ $req->department?->name ?? '-' }}</td>
                        <td class="px-4 py-3 truncate">{{ $req->assignedExpert?->name ?? '-' }}</td>
                        <td class="px-4 py-3">@include('panel.partials.status-badge', ['status' => $req->status])</td>
                        <td class="px-4 py-3 text-gray-500">
                            {{ \Morilog\Jalali\Jalalian::fromDateTime($req->created_at)->format('Y/m/d H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('panel.requests.show', $req->id) }}" class="text-blue-600 hover:underline text-xs">مشاهده</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">هیچ درخواستی یافت نشد</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="md:hidden divide-y">
        @forelse($requests as $req)
            <div class="p-4 space-y-2 text-sm">
                <div class="flex justify-between items-start">
                    <span class="font-mono font-bold text-gray-700">{{ $req->tracking_code }}</span>
                    @include('panel.partials.status-badge', ['status' => $req->status])
                </div>
                <div class="text-gray-500">👤 {{ $req->requester?->name ?? '-' }}</div>
                <div class="text-gray-500">🏛 {{ $req->department?->name ?? '-' }}</div>
                <div class="text-gray-500">👨‍💼 {{ $req->assignedExpert?->name ?? '-' }}</div>
                <a href="{{ route('panel.requests.show', $req->id) }}"
                   class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg text-sm hover:bg-blue-700 transition mt-2">
                    مشاهده درخواست
                </a>
            </div>
        @empty
            <div class="p-8 text-center text-gray-400 text-sm">هیچ درخواستی یافت نشد</div>
        @endforelse
    </div>

    @if($requests->hasPages())
        <div class="p-4 border-t">{{ $requests->links() }}</div>
    @endif
</div>

@endsection