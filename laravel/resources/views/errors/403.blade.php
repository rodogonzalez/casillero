@extends('errors.layout')

@php
  $error_number = 403;
@endphp

@section('title')
  Forbidden.
@endsection

@section('description')

  Please <a href='javascript:history.back()'>go back</a> or return to <a href='/'>our homepage</a>.";  
  
@endsection
