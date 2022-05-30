@extends('layouts.bootstrap')
@section('content')
    <h1>
        @if($location->category)
            @foreach ($location->category as $category)
                {{ $category->emoji }}
            @endforeach
        @endif
        <a href="{{ route('locations.edit', $location) }}">{{ $location->name }}</a>
    </h1>
    <h2>Checkins</h2>
    <ul class="list-group">
        @foreach($checkins as $checkin)
            <a href="{{ route('checkins.edit', $checkin) }}" class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">{{ $checkin->location->name }}</h5>
                    <small class="text-muted">{{ $checkin->checkin_at->diffForHumans() }}</small>
                </div>
                @if($checkin->note)
                    <p class="mb-1"> {{ $checkin->note }}</p>
                @endif
            </a>
        @endforeach
    </ul>
@endsection
