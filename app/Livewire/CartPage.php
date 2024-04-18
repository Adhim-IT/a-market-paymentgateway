<?php

namespace App\Livewire;

use App\Helpers\CartManagement;
use App\Livewire\Partials\Navbar;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Cart - A Market')]
class CartPage extends Component
{
    public $cart_items = [];
    public $grand_total;

    public $sub_total;

    public function mount(){
        $this->cart_items = CartManagement::getCartItemsFromCookie();
        $this->grand_total = CartManagement::calculateGrandTotal($this->cart_items);
        $this->sub_total = CartManagement::sub_total($this->cart_items);
    }
    public function removeItem($product_id){
        $this->cart_items = CartManagement::removCartItem($product_id);
        $this->grand_total = CartManagement::calculateGrandTotal($this->cart_items);
        $this->sub_total = CartManagement::sub_total($this->cart_items);
        $this->dispatch('update-cart-count' , total_count: count($this->cart_items))->to(Navbar::class);
    }

    public function increaseQty($product_id){
        $this->cart_items = CartManagement::incrementItemQuantity($product_id);
        $this->grand_total = CartManagement::calculateGrandTotal($this->cart_items);
        $this->sub_total = CartManagement::sub_Total($this->cart_items);
    }
    public function decreaseQty($product_id){
        $this->cart_items = CartManagement::decrementItemQuantity($product_id);
        $this->grand_total = CartManagement::calculateGrandTotal($this->cart_items);
        $this->sub_total = CartManagement::sub_total($this->cart_items);
    }
    public function render()
    {
        return view('livewire.cart-page');
    }
}
