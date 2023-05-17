@extends(backpack_view('blank'))
@section('content')
    <span>{{$device->name}}</span>
    <h1>Use this code to unLock your locker</h1>
    <h1>{{md5($order_id)}}<h2>
    <a href="/unlock/{{md5($order_id)}}"><img src="{{$qr}}" style="max-height:120px"></a>
@endsection