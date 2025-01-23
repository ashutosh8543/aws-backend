<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "//www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title> Sale report pdf</title>
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
        <th style="width: 200%">{{ __('Order ID') }}</th>
        <th style="width: 200%">{{ __('Amount') }}</th>
        <th style="width: 200%">{{ __('Customer') }}</th>
        <th style="width: 200%">{{ __('Warehouse') }}</th>
        <th style="width: 200%">{{ __('Salesman') }}</th>
        <th style="width: 200%">{{ __('Payment Type') }}</th>   
    </tr>
    </thead>
    <tbody>
    @foreach($sales  as $sale)
        <tr align="center">
        <td>{{date('d-m-Y',strtotime($sale->date))}} {{date('h:i A',strtotime($sale->created_at))}}</td>
        <td>{{$sale->reference_code}}</td>
            <td style="float: left">{{number_format($sale->paid_amount,2)}}</td>
            <td>{{$sale->customer->name}}</td>
            <td>{{$sale->warehouse->name}}</td>
            <td>{{$sale->salesmanDetails->first_name.' '.$sale->salesmanDetails->last_name}}</td>
            @if($sale->payment_type ==1)
                <td>CASH</td>
            @elseif($sale->payment_type== 2)
                <td>CHEQUE</td>
            @elseif($sale->payment_type == 5)
                <td>CREDIT LIMIT</td>
            @endif
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
