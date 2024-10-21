<x-mail::message>
# Order Placed

Your order has been placed successfully.

Order ID: {{ $order->id }}

<x-mail::button :url="$orderUrl">
View Order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
