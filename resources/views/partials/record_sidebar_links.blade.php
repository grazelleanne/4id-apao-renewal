@foreach(['/admin/notification-history'=>'Email History'] as $url=>$label)
<a href="{{ $url }}" class="nav-item">@include('partials.workspace_icon', ['label'=>$label])<span class="nav-label">{{ $label }}</span></a>
@endforeach
