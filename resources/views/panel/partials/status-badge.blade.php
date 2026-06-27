@switch($status)
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