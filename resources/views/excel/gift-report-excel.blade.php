<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "//www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title> gift submitted report pdf</title>
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
        <th style="width: 200%">{{ __('Date') }}</th>
        <th style="width: 200%">{{ __('Gift Issue ID') }}</th>
        <th style="width: 200%">{{ __('Quantity') }}</th>
        <th style="width: 200%">{{ __('messages.pdf.customer') }}</th>  
        <th style="width: 200%">{{ __('messages.pdf.salesman') }}</th>
        <!-- <th style="width: 200%">{{ __('messages.pdf.paid') }}</th> -->
        <!-- <th style="width: 200%">{{ __('messages.pdf.due') }}</th> -->
        <!-- <th style="width: 300%">{{ __('messages.pdf.payment_status') }}</th> -->
    </tr>
    </thead>
    <tbody>
    @foreach($saleReturns  as $saleReturn)
        <tr align="center">
            <td>{{$saleReturn->uploaded_date}}</td>
            <td>{{$saleReturn->unique_id}}</td>
            <td>{{$saleReturn->total_quantity}}</td>
            <td>{{$saleReturn->outlets->name}}</td>
            <td>{{$saleReturn->salesman_details->first_name}} {{$saleReturn->salesman_details->last_name}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
