@extends(backpack_view('blank'))
@section('content')
    <span>{{ $device->name }}</span>
    <h1>{{ __('messages.devicedash.title') }}</h1>
<style>
    .note{

        font-size:10px;
        font-family: Arial, Helvetica, sans-serif;
    }
    </style>

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
                        <span class="note">
                        <?php 

                            $locker_order_linked = App\Models\LockerOrder::whereRaw("raspberry_device_id={$device->id} and gpio_port={$locker_port}")->first();                            
                            if (!is_null($locker_order_linked)){
                                echo "Unlock code : " . md5($locker_order_linked->id);
                            }                        
                        ?>
                        </span>
                    @endif
                    <div class="status">{{ __('messages.' . $data['status']) }} </div>

                </div>
            @endforeach
        @endforeach


    </div>
   
@endsection
