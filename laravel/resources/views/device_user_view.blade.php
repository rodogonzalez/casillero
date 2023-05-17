@extends(backpack_view('blank'))
@section('content')
    <span>{{$device->name}}</span>
    <h1>Lockers Available</h1>
    <div class="row">
    @foreach ($lockers as $locker)        
        <div class='col-4 locker_skin'>            
            @foreach ($locker as $locker_port =>$caption)                
                <a href="/start-locker-request/{{$device->id}}/{{$locker_port}}" class="btn button"> LOCKER [ {{$caption}} ] DISPONIBLE</a>
            @endforeach
        </div>
    @endforeach
    </div>
@endsection