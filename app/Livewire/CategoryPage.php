<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Attributes\Title;
use Livewire\Component;


#[Title('Category Page')]
class CategoryPage extends Component
{
    public function render()
    {
        $categories = Category::where('is_active', true)->get();
        return view('livewire.category-page', [
            'categories' => $categories
        ]);
    }
}
