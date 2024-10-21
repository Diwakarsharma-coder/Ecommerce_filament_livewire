<?php

namespace App\Livewire;

use App\Helpers\CartManagement;
use App\Livewire\Partials\Navbar;
use App\Models\Product;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Product Details')]
class ProductDetailsPage extends Component
{

    use LivewireAlert;
    public $slug;

    public $quantity = 1;

    public function mount($slug)
    {
        $this->slug = $slug;
    }


    public function decrementQty()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addToCart($product_id)
    {
        $total_count = CartManagement::addItemToCartWithQuantity($product_id, $this->quantity);
        $this->dispatch('update-cart-count', total_count: $total_count)->to(Navbar::class);

        $this->alert(
            'success',
            'Product added to cart successfully',
            [
                'position' => 'bottom-right',
                'timer' => 3000,
                'toast' => true,
                'showCancelButton' => false,
                'showConfirmButton' => false,
            ]
        );
    }

    public function incrementQty()
    {
        $this->quantity++;
    }
    public function render()
    {
        $product = Product::where('slug', $this->slug)->first();
        return view('livewire.product-details-page', [
            'product' => $product
        ]);
    }
}
