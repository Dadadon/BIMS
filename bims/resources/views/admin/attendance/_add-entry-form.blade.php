<form method="POST" action="{{ route('admin.attendance.add') }}" class="px-6 py-5 space-y-4">
    @csrf
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Employee <span class="text-red-500">*</span></label>
        <select name="employee_id" required
                class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
            <option value="">— Select —</option>
            @foreach(\App\Models\HR\Employee::active()->orderBy('lastname')->get() as $emp)
            <option value="{{ $emp->id }}">{{ $emp->display_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
            <input type="date" name="log_date" required value="{{ now()->format('Y-m-d') }}"
                   class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Reason</label>
            <select name="reason"
                    class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
                <option>Shift</option>
                <option>Lunch</option>
                <option>Break</option>
                <option>OT</option>
            </select>
        </div>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Clock In <span class="text-red-500">*</span></label>
            <input type="time" name="clock_in" required
                   class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Clock Out</label>
            <input type="time" name="clock_out"
                   class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
        </div>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Comment</label>
        <input type="text" name="comment" placeholder="Optional"
               class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-sm">
    </div>
    <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="document.getElementById('add-entry-modal').classList.add('hidden')"
                class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            Cancel
        </button>
        <button type="submit"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            Add Entry
        </button>
    </div>
</form>
