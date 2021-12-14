@component('mail::message')

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

  <title></title>

  <style type="text/css">
  </style>
</head>
<body style="margin:0; padding:0; background-color:#F2F2F2;">
  <center>
      <p>
          <h3>Customer : {{ $order->customer_name }} </h3>
          <h3>Phone : {{ $order->phone_no }} </h3>
          <h3>Email : {{ $order->email }} </h3>
      </p>
    <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F2F2F2">
        <thead>
            <tr>
                <th>Pizza Name</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Total Price</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td align="center" valign="top">{{ $order->pizza_name }}</td>
                <td align="center" valign="top">{{ $order->qty }}</td>
                <td align="center" valign="top">{{ $order->unit_price }}</td>
                <td align="center" valign="top">{{ $order->total_price }}</td>
                <td align="center" valign="top">{{ $order->status }}</td>
        </tr>
        </tbody>
    </table>
  </center>
</body>
</html>
@component('mail::button', ['url' => ''])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
