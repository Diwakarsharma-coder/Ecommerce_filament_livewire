<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Login')]
class LoginPage extends Component
{
    public $email;
    public $password;

    public function login()
    {
        $this->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required',
        ]);

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            session()->flash('error', 'These credentials do not match our records.');
            return;
        }

        return redirect()->intended();

    }

    public function render()
    {
        return view('livewire.auth.login-page');
    }
}
