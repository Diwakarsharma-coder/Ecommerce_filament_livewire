<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderItem;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Order Details')]
class OrderDetailPage extends Component
{
    #[Url]
    public $order_id;


    public function render()
    {
        $order_items = OrderItem::with('product')->where('order_id', $this->order_id)->get();
        $order = Order::with('address')->find($this->order_id);
        return view('livewire.order-detail-page', compact('order_items', 'order'));
    }
}
