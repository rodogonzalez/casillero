@extends(backpack_view('blank'))
@section('content')
    <span>{{$device->name}}</span>
    <h1>Lockers Available</h1>
    <div class="row">
    @foreach ($lockers as $locker_box)        
        <div class='col-4 locker_skin'>            
            @foreach ($locker_box as $locker_port =>$data)                
                @if ($data['status']=="available")
                <a href="/start-locker-request/{{$device->id}}/{{$locker_port}}" class="btn button">
                      [ {{$data['caption']}} {{$data['status']}} ]
                    
                </a>
                @else
                    [ {{$data['caption']}} {{$data['status']}} ]
                
                @endif
            @endforeach
        </div>
    @endforeach

    </div>
@endsection