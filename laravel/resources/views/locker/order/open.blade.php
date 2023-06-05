@extends(backpack_view('blank'))
@section('content')
    @vite('resources/js/app.js')



    <h1>{{ __('messages.scan.camera') }}</h1>
    <div id="preview">
        
        <form id="frmRequest" action="{{ $signed_payment_link }}" method="post" >           
            
           
            
            <input name="opening_code" id="QR_detected" placeholder="{{ __('messages.enter.code') }}">            
            <input type="submit" class="btn button" value="{{ __('messages.open') }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        </form>

        <hr>
        <div id="video-container">
            <video id="qr-video"></video>
        </div>
    </div>
    <div id="opening_caption" style="display:none;"><h1> {{ __('messages.opening') }}</h1></div>

    <div style="display:none;">

        <div style="display:none;">
            <label>
                Highlight Style
                <select id="scan-region-highlight-style-select">
                    <option value="default-style">Default style</option>
                    <option value="example-style-1">Example custom style 1</option>
                    <option value="example-style-2">Example custom style 2</option>
                </select>
            </label>
            <label>
                <input id="show-scan-region" type="checkbox">
                Show scan region canvas
            </label>
        </div>
        <div>
            <select id="inversion-mode-select">
                <option value="original">Scan original (dark QR code on bright background)</option>
                <option value="invert">Scan with inverted colors (bright QR code on dark background)</option>
                <option value="both">Scan both</option>
            </select>
            <br>
        </div>
        <b>Device has camera: </b>
        <span id="cam-has-camera"></span>
        <br>
        <div>
            <b>Preferred camera:</b>
            <select id="cam-list">
                <option value="environment" selected>Environment Facing (default)</option>
                <option value="user">User Facing</option>
            </select>
        </div>
        <b>Camera has flash: </b>
        <span id="cam-has-flash"></span>
        <div>
            <button id="flash-toggle">📸 Flash: <span id="flash-state">off</span></button>
        </div>
        <br>
        <b>Detected QR code: </b>
        <span id="cam-qr-result">None</span>
        <br>
        <b>Last detected at: </b>
        <span id="cam-qr-result-timestamp"></span>
        <br>
        <button id="start-button">Start</button>
        <button id="stop-button">Stop</button>
        <hr>

        <h1>Scan from File:</h1>
        <input type="file" id="file-selector">
        <b>Detected QR code: </b>
        <span id="file-qr-result">None</span>
    </div>
@endsection
