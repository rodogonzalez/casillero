@extends(backpack_view('blank'))
@section('content')
    <span>{{$device->name}}</span>
    <h1>Open this QR to unLock your locker</h1>
    <a href="{{$url}}"><img src="{{$qr}}" style="max-height:120px"></a>
@endsection