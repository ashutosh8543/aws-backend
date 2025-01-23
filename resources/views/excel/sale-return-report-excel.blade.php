<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "//www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title> Sale return report pdf</title>
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
        <th style="width: 200%">{{ __('Return ID') }}</th>
        <th style="width: 200%">{{ __('Order ID') }}</th>
        <th style="width: 200%">{{ __('Amount') }}</th>
        <th style="width: 200%">{{ __('Customer') }}</th>
        <th style="width: 200%">{{ __('Warehouse') }}</th>
        <th style="width: 200%">{{ __('Salesman') }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($saleReturns  as $saleReturn)
        <tr align="center">
            <td>{{date('d-m-Y',strtotime($saleReturn->date))}} {{date('h:i A',strtotime($saleReturn->created_at))}}</td>
            <td>{{$saleReturn->reference_code}}</td>
            <td>{{$saleReturn->sale->reference_code}}</td>
            <td style="float: left">{{$saleReturn->countryDetails->currencyDetails->symbol??''}} {{number_format($saleReturn->paid_amount,2)}}</td>
            <td>{{$saleReturn->customer->name}}</td>
            <td>{{$saleReturn->warehouse->name}}</td>
            <td>{{$saleReturn->salesmanDetails->first_name.' '.$saleReturn->salesmanDetails->last_name}}</td>  
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
