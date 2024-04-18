<?php

namespace App\Livewire;

use App\Helpers\CartManagement;
use App\Mail\OrderPlaced;
use App\Models\Address;
use App\Models\Order;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Stripe;

class CheckoutPage extends Component
{
    public $first_name;
    public $last_name;
    public $street_address;
    public $phone;
    public $city;
    public $state;
    public $zip_code;
    public $payment_method;
    public $shipping_method_id;

    public $select_shipping = null;



    public function mount()
    {

        $firstShippingMethod = ShippingMethod::first();
        if ($firstShippingMethod) {
            $this->shipping_method_id = $firstShippingMethod->id;
        }
    }

    public function placeOrder()
    {
        if ($this->select_shipping) {
            $this->shipping_method_id = $this->select_shipping;
        }

        $this->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'required',
            'street_address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip_code' => 'required',
            'payment_method' => 'required',
            'shipping_method_id' => 'required|exists:shipping_methods,id',
        ]);


        $cartItems = CartManagement::getCartItemsFromCookie();


        $shippingMethod = ShippingMethod::find($this->shipping_method_id);


        $grandTotal = CartManagement::calculateGrandTotal($cartItems) + $shippingMethod->cost;

        if ($this->payment_method == 'stripe') {

            $redirectUrl = $this->createStripeCheckoutSession($grandTotal, $cartItems);
        } else {

            $redirectUrl = route('success');
        }

        return redirect($redirectUrl);
    }

    public function createStripeCheckoutSession($total, $cartItems)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));


        foreach ($cartItems as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => $item['unit_amount'] * 100,
                    'product_data' => [
                        'name' => $item['name'],
                    ],
                ],
                'quantity' => $item['quantity'],
            ];
        }


        $shippingMethod = ShippingMethod::find($this->shipping_method_id);
        $lineItems[] = [
            'price_data' => [
                'currency' => 'usd',
                'unit_amount' => $shippingMethod->cost * 100,
                'product_data' => [
                    'name' => $shippingMethod->name,
                ],
            ],
            'quantity' => 1,
        ];


        $sessionCheckout = CheckoutSession::create([
            'payment_method_types' => ['card'],
            'customer_email' => auth()->user()->email,
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('cancel'),
        ]);

        return $sessionCheckout->url;
    }



    public function render()
    {
        $cartItems = CartManagement::getCartItemsFromCookie();
        $shippingMethods = ShippingMethod::all();
        $grandTotal = CartManagement::calculateGrandTotal($cartItems);
        $subTotal = CartManagement::sub_total($cartItems);


        $selectedShippingMethod = $shippingMethods->firstWhere('id', $this->shipping_method_id);
        $shippingMethodCost = $selectedShippingMethod ? $selectedShippingMethod->cost : 0;


        $total = CartManagement::calculateTotal($cartItems, $shippingMethodCost);

        return view('livewire.checkout-page', [
            'shippingMethodCost' => $shippingMethodCost,
            'shippingMethods' => $shippingMethods,
            'cartItems' => $cartItems,
            'grandTotal' => $grandTotal,
            'subTotal' => $subTotal,
            'total' => $total,
        ]);
    }

}
