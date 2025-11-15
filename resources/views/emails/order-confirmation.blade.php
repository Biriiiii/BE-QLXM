<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Xác nhận đơn hàng</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }

        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 0 0 5px 5px;
        }

        .order-info {
            background-color: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .item-table th,
        .item-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .item-table th {
            background-color: #f1f1f1;
            font-weight: bold;
        }

        .total {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
            margin-top: 15px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 5px;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <h2>Xác nhận đơn hàng #{{ $order->id }}</h2>
    </div>

    <div class="content">
        <p>Xin chào <strong>{{ $customer->name }}</strong>,</p>
        <p>Cảm ơn bạn đã đặt hàng tại {{ config('app.name') }}. Đơn hàng của bạn đã được tiếp nhận và đang được xử lý.
        </p>

        <div class="order-info">
            <h3>Thông tin đơn hàng</h3>
            <p><strong>Mã đơn hàng:</strong> #{{ $order->id }}</p>
            <p><strong>Ngày đặt:</strong> {{ $order->order_date->format('d/m/Y H:i') }}</p>
            <p><strong>Trạng thái:</strong>
                @switch($order->status)
                    @case('pending')
                        <span style="color: #ffc107;">Chờ xử lý</span>
                    @break

                    @case('confirmed')
                        <span style="color: #28a745;">Đã xác nhận</span>
                    @break

                    @case('shipped')
                        <span style="color: #17a2b8;">Đang giao hàng</span>
                    @break

                    @case('delivered')
                        <span style="color: #28a745;">Đã giao</span>
                    @break

                    @case('cancelled')
                        <span style="color: #dc3545;">Đã hủy</span>
                    @break

                    @default
                        {{ $order->status }}
                @endswitch
            </p>
        </div>

        <div class="order-info">
            <h3>Thông tin giao hàng</h3>
            <p><strong>Tên:</strong> {{ $customer->name }}</p>
            <p><strong>Số điện thoại:</strong> {{ $customer->phone }}</p>
            @if ($customer->email)
                <p><strong>Email:</strong> {{ $customer->email }}</p>
            @endif
            <p><strong>Địa chỉ:</strong> {{ $customer->address }}</p>
        </div>

        <div class="order-info">
            <h3>Chi tiết sản phẩm</h3>
            <table class="item-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Đơn giá</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->price, 0, ',', '.') }}đ</td>
                            <td>{{ number_format($item->price * $item->quantity, 0, ',', '.') }}đ</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="total">
                <p>Tổng cộng: {{ number_format($order->total_amount, 0, ',', '.') }}đ</p>
            </div>
        </div>

        <p>Chúng tôi sẽ liên hệ với bạn sớm nhất để xác nhận và giao hàng.</p>
        <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi qua:</p>
        <ul>
            <li>Email: {{ config('mail.from.address') }}</li>
            <li>Điện thoại: 1900-xxxx</li>
        </ul>
    </div>

    <div class="footer">
        <p>Email này được gửi tự động, vui lòng không trả lời.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </div>
</body>

</html>
