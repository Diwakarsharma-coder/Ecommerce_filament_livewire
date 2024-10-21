<?php

use App\Livewire\Auth\ForgotPasswordPage;
use App\Livewire\Auth\LoginPage;
use App\Livewire\Auth\RegisterPage;
use App\Livewire\Auth\ResetPasswordPage;
use App\Livewire\CancelPage;
use App\Livewire\CartPage;
use App\Livewire\CategoryPage;
use App\Livewire\CheckoutPage;
use App\Livewire\HomePage;
use App\Livewire\MyOrderPage;
use App\Livewire\OrderDetailPage;
use App\Livewire\ProductDetailsPage;
use App\Livewire\ProductPage;
use App\Livewire\SuccessPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePage::class)->name('home');
Route::get('/products', ProductPage::class)->name('product');
Route::get('/products/{slug}', ProductDetailsPage::class)->name('product-details');
Route::get('/cart', CartPage::class)->name('cart');
Route::get('/categories', CategoryPage::class)->name('category');



Route::middleware('guest')->group(function () {
    Route::get('/login', LoginPage::class)->name('login');
    Route::get('/register', RegisterPage::class)->name('register');
    Route::get('/forgot', ForgotPasswordPage::class)->name('forgot-password');
    Route::get('/reset-password/{token}', ResetPasswordPage::class)->name('password.reset');
});


Route::middleware('auth')->group(function () {

    Route::get('/logout', function () {
        Auth::logout();
        return redirect()->route('home');
    })->name('logout');

    Route::get('/checkout', CheckoutPage::class)->name('checkout');
    Route::get('/orders', MyOrderPage::class)->name('orders');
    Route::get('/order/{order_id}', OrderDetailPage::class)->name('order-detail');

    Route::get('/success', SuccessPage::class)->name('success');
    Route::get('/cancel', CancelPage::class)->name('cancel');
});




