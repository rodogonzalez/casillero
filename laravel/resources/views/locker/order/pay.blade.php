@extends(backpack_view('blank'))

@section('content')
    <h1>{{ __('messages.payment.title') }}</h1>
    <h1> {{ __('messages.order.title') }} : {{ $Order->id }}</h1>
    <div class="billing_fields">
        <div>{{ env('HOUR_RATE') }} : {{ __('messages.payment.title.hour_rate') }}</div>
        <div>{{ __('messages.payment.title.hour_rate') }}</div>
        <div>Total{{ __('messages.payment.title.time_used') }}}:{{ $Order->current_duration->hours }} horas {{ $Order->current_duration->minutes }} minutos</div>
        <div class="row">Inicio: {{ $Order->opening_paid_at }}</div>
        <div class="row">Fin: {{ now() }}</div>
        <div>Total{{ __('messages.payment.title.billable') }}}: 
        {{ $time_billabled }}
        </div>

        <button onclick="alert('keep working :) ')">{{ __('messages.pay') }}</button>
    </div>
@endsection
