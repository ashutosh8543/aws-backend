<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "//www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title> Mileage submitted report pdf</title>
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
        <th style="width: 200%">{{ __('date') }}</th>
        <th style="width: 200%">{{ __('messages.pdf.salesman') }}</th>
        <th style="width: 200%">{{ __('mileage') }}</th>
        <th style="width: 200%">{{ __('type') }}</th>
        <th style="width: 200%">{{ __('location') }}</th>
        <!-- <th style="width: 200%">{{ __('messages.pdf.paid') }}</th> -->
        <!-- <th style="width: 200%">{{ __('messages.pdf.due') }}</th> -->
        <!-- <th style="width: 300%">{{ __('messages.pdf.payment_status') }}</th> -->
    </tr>
    </thead>
    <tbody>
    @foreach($saleReturns  as $saleReturn)
        <tr align="center">
            <td>{{date('d-m-Y',strtotime($saleReturn->uploaded_date))}} {{date('h:i A',strtotime($saleReturn->uploaded_date))}}</td>
            <td>{{$saleReturn->sales_man->first_name}} {{$saleReturn->sales_man->last_name}}</td>
            <td>{{$saleReturn->mileage}} KM</td>
            <td>{{$saleReturn->type}}</td>
            <td>{{$saleReturn->location}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
