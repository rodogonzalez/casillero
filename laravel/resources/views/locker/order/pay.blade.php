@extends(backpack_view('blank'))

@section('content')

    <script>
        /*TODO: Move this code inside to a compiled JS file to be mixed */
        function check_payment_status() {
            console.log("checking ....");

            var url = '{{ route("payment_status", ["order_id" => $Order->id]); }}' ;
            fetch(url)
                .then(response => response.json())
                .then(json => {
                    if (json.paid) 
                    {
                        //redirect 
                        $("#payment_details").hide();
                        $("#payment_thanks").show();
                        return ;


                    }
                    ;

                    // Do stuff with the contents of the JSON file here

                });
            time_W = 5000;

            setTimeout(check_payment_status, time_W);
        }

        check_payment_status();
    </script>

    <div class="container">

        <h1>{{ __('messages.payment.title') }} {{ __('messages.order.title') }} : {{ $Order->id }}</h1>
        <hr>

        <div class='row'>

            <div class="col-4">

                <div class="row">
                    <div class='col-6'>
                        {{ __('messages.payment.title.time_used') }}
                    </div>
                    <div class='col-6'>
                        {{ $Order->duration->hours }} horas {{ $Order->duration->minutes }} minutos
                    </div>
                </div>

                <div class="row">
                    <div class='col-6'>
                        {{ __('messages.payment.title.hour_rate') }}
                    </div>
                    <div class='col-6'>
                        {{ env('BLOCKBEE_FIAT_SYMBOL') }} {{ env('HOUR_RATE') }} {{ env('COINPAYMENT_CURRENCY') }}
                    </div>
                </div>

                <div class="row">
                    <div class='col-6'>Inicio: </div>
                    <div class='col-6'>{{ $Order->opening_paid_at }}</div>
                </div>
                <div class="row">
                    <div class='col-6'>Fin: </div>
                    <div class='col-6'>{{ now() }}</div>
                </div>


                <div class="row">
                    <div class='col-6'>
                        {{ __('messages.payment.title.billable') }}
                    </div>
                    <div class='col-6'>
                        {{ $time_billabled }}
                    </div>
                </div>

            </div>

            <div class="col-4">
                <h1>Locker #: {{ $locker_number }}</h1>

                <a id="payment_thanks" href="{{ $unlock_link }}" style="display:none;">HAGA CLICK PARA ABRIR EL LOCKER! <br>ABRIR</a>


                <div id="payment_details" class="row">
                    <div class='col-6'>
                        Direccion Destino:<br>
                        Amount :<br>
                        Exchange Rate :<br>
                        Fiat Equivalent :<br>
                    </div>
                    <div class='col-6'>
                        <small> {{ $wallet_addr }}</small><br>
                        {{ $amount->value_coin }} {{ env('BLOCKBEE_COIN') }}<br>
                        ${{ $amount->exchange_rate }} usd.<br>
                        ${{ $fiat_amount }} usd<br>
                    </div>
                    <img src="{{ $qr }}" style="max-height:120px">
                </div>
                

            </div>

        </div>
    </div>
@endsection
