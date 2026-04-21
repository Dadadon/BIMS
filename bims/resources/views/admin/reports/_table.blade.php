@if(empty($rows))
<div class="rounded-lg bg-white shadow p-12 text-center">
    <p class="text-sm text-gray-400">No data matched your filters.</p>
</div>
@else
<div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    @foreach($columns as $col)
                    <th class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">
                        {{ $col['label'] }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach($rows as $row)
                @php $row = (array) $row; @endphp
                <tr class="hover:bg-gray-50">
                    @foreach($columns as $col)
                    <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-700">
                        @php $val = $row[$col['key']] ?? null; @endphp
                        {{ is_numeric($val) && str_contains($col['label'], 'Pay') || str_contains($col['label'], 'Points') || str_contains($col['label'], 'Salary') || str_contains($col['label'], 'Net') || str_contains($col['label'], 'Gross') || str_contains($col['label'], 'Tax') || str_contains($col['label'], 'Commission')
                            ? number_format((float)$val, 2)
                            : ($val ?? '—') }}
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<p class="mt-2 text-xs text-gray-400 text-right">Showing {{ count($rows) }} record(s). Export to CSV for full dataset.</p>
@endif
