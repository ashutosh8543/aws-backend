<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "//www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title> Stock report excel</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon.ico') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Fonts -->
    <!-- General CSS Files -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
</head>
<body>
<table width="100%" cellspacing="0" cellpadding="10" style="margin-top: 40px;">
    <thead>
    <tr style="background-color: dodgerblue;">
        <th style="width: 200%">{{ __('Code') }}</th>
        <th style="width: 300%">{{ __('Name') }}</th>
        <th style="width: 300%">{{ __('Warehouse') }}</th>        
        <th style="width: 250%">{{ __('messages.pdf.current_stock') }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($stocks  as $stock)
        <tr align="center">
            <td>{{$stock->product->code}}</td>
            <td>{{$stock->product->name}}</td>
            <td>{{$stock->warehouse->name}}</td>
            <td>{{$stock->warehouse_quantities}} {{$stock->product->productUnit->name??''}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
