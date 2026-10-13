@include('partials.records_layout_start',['title'=>ucfirst($module).' Records'])
@if($module==='firearms')<p>Personnel screens retain the originally registered pistol. Manage additional firearm records here. ATP numbers must contain 3–100 letters, numbers, slashes or hyphens.</p>@endif
@if($module==='ammunition')<p>Personnel screens retain the original ammunition quantity. Manage additional lots here.</p>@endif
@if($module!=='personnel')
<details @if($errors->any()) open @endif><summary>Add {{ rtrim($module,'s') }} record</summary>
<form method="post" action="/admin/records/{{ $module }}">@csrf
@include('partials.record_fields',['record'=>null])<button>Save record</button></form></details>
@endif
<div class="scroll"><table><thead><tr><th>ID</th>@foreach($headers as $field)<th>{{ ucwords(str_replace('_',' ',$field)) }}</th>@endforeach @if($module!=='personnel')<th>Actions</th>@endif</tr></thead><tbody>
@forelse($records as $record)<tr><td>{{ $record->id }}</td>@foreach($headers as $field)<td>{{ $record->getRawOriginal($field) }}</td>@endforeach
@if($module!=='personnel')<td><details><summary>Edit</summary><form method="post" action="/admin/records/{{ $module }}/{{ $record->id }}">@csrf @method('PUT')@include('partials.record_fields')<button>Save changes</button></form></details>
<form method="post" action="/admin/records/{{ $module }}/{{ $record->id }}">@csrf @method('DELETE')<button>Delete record</button></form></td>@endif</tr>@empty<tr><td class="empty-state" colspan="{{ count($headers) + ($module!=='personnel' ? 2 : 1) }}">No {{ $module }} records yet.</td></tr>@endforelse
</tbody></table></div>{{ $records->links() }}
@if($module==='personnel')<p>Use the existing <a href="/admin/personnel">personnel screen</a> to edit or archive personnel. IDs above are used in firearm and ammunition imports.</p>@endif
</main></body></html>
