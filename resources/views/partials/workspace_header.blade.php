@php
$user = (object) request()->attributes->get('apao_user', []);
$notificationPrefix = $user->role === 'staff' ? '/staff' : '/admin';
@endphp
<div class="workspace-header-actions">
<div class="workspace-notifications" data-notification-url="{{ $notificationPrefix }}/notifications" data-read-url="{{ $notificationPrefix }}/notifications/read">
<button type="button" class="workspace-bell" aria-label="Notifications" aria-expanded="false"><svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0v1a3 3 0 0 1-6 0v-1m6 0H9M12 3v2"/></svg><span class="workspace-unread" hidden></span></button>
<div class="workspace-notification-menu" hidden><div class="workspace-notification-heading">Notifications<button type="button" class="workspace-mark-read">Mark all read</button></div><div class="workspace-notification-list" role="status">Loading notifications…</div></div>
</div>
@include('partials.account_dropdown')
</div>
<script src="{{ asset('js/workspace-notifications.js') }}" defer></script>
