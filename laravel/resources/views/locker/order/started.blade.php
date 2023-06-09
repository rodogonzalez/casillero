@extends(backpack_view('blank'))
@section('content')
    <span>{{ $device->name }}</span>
    <h1>{{ __('messages.use_the_qr') }}</h1>
    <h2>Locker #{{ $locker_number }}</h2>
    <input id="qr_code" type="text" readonly=readonly value="{{ md5($order_id) }}">
    <a href="#" onclick="copyToClipboard('{{ md5($order_id) }}'); return false;">Copiar</a>
    <hr>
    <img src="{{ $qr }}" style="max-height:120px">
    <a href="#">
        <h3>{{ __('messages.print') }}</h3>
    </a>
    <script>
        function copyToClipboard(element) {
            var $temp = $("#qr_code");
            //$("body").append($temp);
            $temp.val(element).select();
            document.execCommand("copy");
            
        }
    </script>
@endsection
