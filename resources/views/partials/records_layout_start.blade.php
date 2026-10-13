<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="csrf-token" content="{{ csrf_token() }}"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{ $title }} — APAO</title><link rel="stylesheet" href="{{ asset('css/record-management.css') }}"></head><body class="records-admin light-mode">
@php
 $staffWorkspace=request()->is('staff/*');
 $dashboardUrl=$staffWorkspace?'/staff/dashboard':'/admin/dashboard';
 $workspaceLinks=$staffWorkspace?['/staff/dashboard'=>'Dashboard','/staff/dashboard?page=registration'=>'New Registration','/staff/dashboard?page=personnel'=>'List of Personnel','/staff/dashboard?page=ics'=>'ICS','/staff/dashboard?page=par'=>'PAR','/staff/dashboard?page=renewal'=>'Renewal','/staff/dashboard?page=report'=>'Report','/staff/dashboard?page=archive'=>'Archive Data','/staff/email-history'=>'Email History']:['/admin/dashboard'=>'Dashboard','/admin/personnel'=>'List of Personnel','/admin/inspection'=>'Inspection / Renewal','/admin/reports'=>'Reports','/admin/archive'=>'Archive Data','/admin/users'=>'Manage Users','/admin/audit'=>'Audit Log','/admin/notification-history'=>'Email History'];
@endphp
<aside class="records-sidebar"><a class="records-brand" href="{{ $dashboardUrl }}"><img src="{{ asset('images/logo.png') }}" alt="APAO logo"><span>4ID APAO Firearms<small>{{ $staffWorkspace?'STAFF WORKSPACE':'ADMIN WORKSPACE' }}</small></span></a><nav aria-label="Workspace navigation">
@foreach($workspaceLinks as $url=>$label)
<a href="{{ $url }}" class="records-nav-link {{ request()->is(ltrim($url,'/')) ? 'is-active' : '' }}" @if(request()->is(ltrim($url,'/'))) aria-current="page" @endif>@include('partials.workspace_icon', ['label'=>$label])<span>{{ $label }}</span></a>
@endforeach</nav><div class="records-sidebar-footer">10th Field Property Accountability Office</div></aside>
<main class="records-main"><div class="records-topbar">@include('partials.workspace_header')</div><div class="records-page-heading"><span class="records-eyebrow">RECORD MANAGEMENT</span><h1>{{ $title }}</h1></div>
@if(session('status'))<p class="status" role="status">{{ session('status') }}</p>@endif
@if($errors->any())<div class="error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
