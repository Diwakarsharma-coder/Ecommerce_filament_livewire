<?php

namespace App\Livewire;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My Orders')]
class MyOrderPage extends Component
{
    public function render()
    {
        $myOrders = Order::where('user_id', Auth::user()->id);
        $myOrders = $myOrders->paginate(5);

        return view('livewire.my-order-page', compact('myOrders'));
    }
}
