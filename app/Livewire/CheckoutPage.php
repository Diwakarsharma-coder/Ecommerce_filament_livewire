<?php

namespace App\Livewire;

use App\Helpers\CartManagement;
use App\Mail\OrderPlaced;
use App\Models\Address;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Title;
use Livewire\Component;
use Stripe\Checkout\Session;
use Stripe\Stripe;

#[Title('Checkout')]
class CheckoutPage extends Component
{

    public $first_name;
    public $last_name;
    public $phone;
    public $street_address;
    public $city;
    public $state;
    public $country;
    public $zip_code;
    public $payment_method;

    public function mount(){
        $cart_items = CartManagement::getCartItemsFromCookie();
        if(count($cart_items) == 0){
            return redirect()->route('product');
        }
    }

    public function placeOrder()
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'street_address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'zip_code' => 'required|string|max:10',
            'payment_method' => 'required|string',
        ]);


        $cart_items = CartManagement::getCartItemsFromCookie();
        $total_amount = CartManagement::calculateGrandTotal($cart_items);
        $inline_items = [];

        foreach($cart_items as $item){
            $inline_items[]=[
                'price_data'=>[
                    'currency'=>'inr',
                    'unit_amount'=>$item['unit_amount']*100,
                    'product_data'=>[
                        'name'=>$item['name']
                    ]
                ],
                'quantity'=>$item['quantity']
            ];
        }


        $order = new Order();
        $order->user_id = Auth::user()->id;
        $order->grand_total = $total_amount;
        $order->payment_method = $this->payment_method;
        $order->payment_status = 'pending';
        $order->status = 'new';
        $order->currency = 'inr';
        $order->shipping_amount = 0;
        $order->shipping_method = 'none';
        $order->notes = 'Order placed by '.Auth::user()->name;



        $address = new Address();
        $address->first_name = $this->first_name;
        $address->last_name = $this->last_name;
        $address->phone = $this->phone;
        $address->street_address = $this->street_address;
        $address->city = $this->city;
        $address->state = $this->state;
        $address->country = $this->country;
        $address->zip_code = $this->zip_code;


        $redirect_url ='';

        if($this->payment_method == 'stripe'){
            Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
            $checkout_session = Session::create([
                'payment_method_types' => ['card'],
                'customer_email' => Auth::user()->email,
                'line_items' => $inline_items,
                'mode' => 'payment',
                'success_url' => route('success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('cancel'),
            ]);
            $redirect_url = $checkout_session->url;


        }else{
            $redirect_url = route('success');
        }

        $order->save();
        $address->order_id = $order->id;
        $address->save();

        $order->items()->createMany($cart_items);
        CartManagement::clearCartItem();

        Mail::to(Auth::user()->email)->send(new OrderPlaced($order));
        return redirect($redirect_url);

    }

    public function render()
    {
        $cart_items = CartManagement::getCartItemsFromCookie();
        $total_amount = CartManagement::calculateGrandTotal($cart_items);

        return view('livewire.checkout-page', compact('cart_items', 'total_amount'));
    }
}
