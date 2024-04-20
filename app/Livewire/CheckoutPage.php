<?php

namespace App\Livewire;

use App\Helpers\CartManagement;
use App\Mail\OrderPlaced;
use App\Models\Address;
use App\Models\Order;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Stripe\Checkout\Session;
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
        $cartItems = CartManagement::getCartItemsFromCookie();
        if (count($cartItems) == 0) {
            return redirect('/products');
        }
    }

    public function placeOrder()
    {
        $redirect_url = '';

        // Mendapatkan semua item dalam keranjang belanja
        $cartItems = CartManagement::getCartItemsFromCookie();

        // Validasi data pesanan
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

        // Mendapatkan metode pengiriman yang dipilih
        $selectedShippingMethod = ShippingMethod::findOrFail($this->shipping_method_id);

        // Menghitung biaya pengiriman
        $shippingCost = $selectedShippingMethod->cost;

        // Simpan pesanan
        $order = new Order();
        $order->user_id = auth()->user()->id;
        $order->grand_total = CartManagement::calculateGrandTotal($cartItems) + $shippingCost; // Total termasuk biaya pengiriman
        $order->sub_total = CartManagement::sub_total($cartItems);
        $order->payment_status = 'pending';
        $order->status = 'new';
        $order->currency = 'usd';
        $order->shipping_amount = $shippingCost; // Menyimpan biaya pengiriman
        $order->shipping_method_id = $this->shipping_method_id;
        $order->payment_method = $this->payment_method;
        $order->notes = 'Order placed by ' . auth()->user()->name;
        $order->save();

        // Simpan alamat pengiriman
        $address = new Address();
        $address->first_name = $this->first_name;
        $address->last_name = $this->last_name;
        $address->street_address = $this->street_address;
        $address->phone = $this->phone;
        $address->city = $this->city;
        $address->state = $this->state;
        $address->zip_code = $this->zip_code;
        $address->order_id = $order->id;
        $address->save();

        // Jika pembayaran menggunakan Stripe
        if ($this->payment_method == 'stripe') {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            $lineItems = [];

            // Tambahkan barang-barang dari keranjang belanja sebagai line items
            foreach ($cartItems as $item) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => $item['unit_amount'] * 100, // Harga barang dalam sen (cent)
                        'product_data' => [
                            'name' => $item['name'],
                        ],
                    ],
                    'quantity' => $item['quantity'],
                ];
            }

            // Tambahkan informasi biaya pengiriman sebagai line item tambahan jika ada
            if ($shippingCost > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => $shippingCost * 100, // Biaya pengiriman dalam sen (cent)
                        'product_data' => [
                            'name' => ShippingMethod::find($this->shipping_method_id)->name,
                        ],
                    ],
                    'quantity' => 1, // Biaya pengiriman dihitung per pesanan, bukan per item
                ];
            }

            // Buat sesi pembayaran Stripe dengan line items yang sudah ditambahkan
            $sessionCheckout = Session::create([
                'payment_method_types' => ['card'],
                'customer_email' => auth()->user()->email,
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('cancel'),
            ]);
            $redirect_url = $sessionCheckout->url;
        } else {
            $redirect_url = route('success');
        }

        // Hapus item keranjang belanja setelah pesanan berhasil ditempatkan
        CartManagement::clearCartItems();

        // Redirect pengguna ke halaman pembayaran
        return redirect($redirect_url);
    }


    public function render()
    {
        $cartItems = CartManagement::getCartItemsFromCookie();
        $shippingMethods = ShippingMethod::all();
        $grandTotal = CartManagement::calculateGrandTotal($cartItems);
        $subTotal = CartManagement::sub_total($cartItems);

        $selectedShippingMethod = $shippingMethods->firstWhere('id', $this->shipping_method_id);
        $shippingMethodCost = $selectedShippingMethod ? $selectedShippingMethod->cost : 0;

        $total = $grandTotal + $shippingMethodCost;

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
