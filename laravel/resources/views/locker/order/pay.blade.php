@extends(backpack_view('blank'))

@section('content')
    <h1>{{ __('messages.payment.title') }}</h1>
    <h1> {{ __('messages.order.title') }} : {{ $Order->id }}</h1>
    <div class="billing_fields">
        <div class="row">{{ env('HOUR_RATE') }} {{ env('COINPAYMENT_CURRENCY') }}: {{ __('messages.payment.title.hour_rate') }}</div>
        <div class="row">{{ __('messages.payment.title.hour_rate') }}</div><hr>
        <div class="row">Total{{ __('messages.payment.title.time_used') }}} : {{ $Order->current_duration->hours }} horas {{ $Order->current_duration->minutes }} minutos</div>
        <div class="row">Inicio: {{ $Order->opening_paid_at }}</div>
        <div class="row">Fin: {{ now() }}</div>
        <div>Total{{ __('messages.payment.title.billable') }}}: 
            {{ $time_billabled }}
        </div>

        <a href="{{ $payment_url }}"><h1>{{ __('messages.pay') }}</h1></a>
    </div>
@endsection
