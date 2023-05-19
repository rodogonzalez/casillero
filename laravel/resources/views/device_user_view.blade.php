@extends(backpack_view('blank'))
@section('content')
    <span>{{ $device->name }}</span>
    <h1>Lockers Available</h1>
    <div class="lockers-container">

        @foreach ($lockers as $locker_box)
            @foreach ($locker_box as $locker_port => $data)
                <div class='locker {{ $data['status'] }}'>
                    {{ $data['caption'] }}
                    @if ($data['status'] == 'available')
                        <a href="{{ $data['link'] }}" class="btn button">
                            <i class="la la-unlock"></i>[Open]
                        </a>
                    @else
                        <i class="la la-lock"></i> - locked
                    @endif
                    <br>{{ $data['status'] }}
                </div>
            @endforeach
        @endforeach


    </div>
@endsection
