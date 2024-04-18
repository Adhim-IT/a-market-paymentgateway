<?php

namespace App\Livewire\Auth;

use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

#[Title('Login')]
class LoginPage extends Component
{
    public $name;
    public $password;

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255|exists:users,name',
            'password' => 'required|string|min:6|max:255',
        ]);

        if (!Auth::attempt(['name' => $this->name, 'password' => $this->password])) {
            session()->flash('error', 'Wrong credentials');
            return;
        }


        return redirect()->intended();
    }

    public function render()
    {
        return view('livewire.auth.login-page');
    }
}
