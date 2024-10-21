<?php

namespace App\Livewire;

use App\Helpers\CartManagement;
use App\Livewire\Partials\Navbar;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Product Page')]
class ProductPage extends Component
{

    use LivewireAlert;
    use WithPagination;
    #[Url]
    public $selectedCategory = [];
    #[Url]
    public $selectedBrand = [];

    #[Url]
    public $is_featured = false;

    #[Url]
    public $on_sale = false;


    #[Url]
    public $price_range = 300000;

    #[Url]
    public $sort_by = 'latest';


    public function addToCart($product_id)
    {
        $total_count = CartManagement::addItemToCart($product_id);
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

    public function render()
    {
        $products = Product::query()->where('is_active', true);

        if ($this->selectedCategory) {
            $products->whereIn('category_id', $this->selectedCategory);
        }

        if ($this->selectedBrand) {
            $products->whereIn('brand_id', $this->selectedBrand);
        }

        if ($this->is_featured) {
            $products->where('is_featured', true);
        }

        if ($this->on_sale) {
            $products->where('on_sale', true);
        }

        if ($this->price_range) {
            $products->whereBetween('price', [0, $this->price_range]);
        }


        if ($this->sort_by == 'price-asc') {
            $products->orderBy('price', 'asc');
        }

        if ($this->sort_by == 'price-desc') {
            $products->orderBy('price', 'desc');
        }

        if ($this->sort_by == 'latest') {
            $products->latest();
        }

        $products = $products->paginate(5);


        $categories = Category::where('is_active', true)->get(['id', 'name', 'slug']);
        $brands = Brand::where('is_active', true)->get(['id', 'name', 'slug']);

        return view('livewire.product-page', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands
        ]);
    }
}
