@extends(backpack_view('blank'))
@section('content')
    <span>{{ $device->name }}</span>
    <h1>{{ __('messages.devicedash.title') }}</h1>


    <div class="lockers-container">

        @foreach ($lockers as $locker_box)
            @foreach ($locker_box as $locker_port => $data)
                <div class='locker {{ $data['status'] }}'>
                    {{ $data['caption'] }}
                    @if ($data['status'] == 'available')
                        <i class="la la-unlock"></i>
                        <a href="{{ $data['link'] }}" class="btn button">
                            {{ __('messages.use') }}
                        </a>
                    @else
                        <i class="la la-lock"></i> <a href="/open">[ {{ __('messages.open') }} ]</a>
                    @endif
                    <div class="status">{{ __('messages.' . $data['status']) }} </div>

                </div>
            @endforeach
        @endforeach


    </div>
    @if (env('ADSENSE_ENABLED', false))
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
        <ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-3258144106657369" data-ad-slot="8812111908"
            data-ad-format="auto" data-full-width-responsive="true"></ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    @endif
@endsection
