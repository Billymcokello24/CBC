@extends('emails.layouts.master')

@section('content')
<h1>{{ $mailSubject }}</h1>

<p>{{ $greeting }},</p>

@foreach($lines as $line)
    <p>{{ $line }}</p>
@endforeach

@if($action)
    <div class="button-wrapper">
        <a href="{{ $action['url'] }}" class="button">{{ $action['label'] }}</a>
    </div>
@endif

<div class="divider"></div>

<p>Regards,<br>The {{ config('app.name') }} Team</p>
@endsection
