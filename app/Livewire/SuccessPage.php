<?php
namespace App\Livewire;

use App\Models\Order;
use App\Models\ShippingMethod;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Stripe\Checkout\Session;
use Stripe\Stripe;

#[Title('Success - A Market')]
class SuccessPage extends Component
{
    #[Url]
    public $session_id;

    public function render()
    {
        $latest_order = Order::with('address')->where('user_id', auth()->user()->id)->latest()->first();
        $shipping_method_cost = '';
        if ($latest_order) {
            $shipping_method_id = $latest_order->shipping_method_id;
            $shipping_method = ShippingMethod::find($shipping_method_id);
            if ($shipping_method) {
                $shipping_method_cost = $shipping_method->cost;
            }
        }
        $shipping_method_name = '';
        if ($latest_order) {
            $shipping_method_id = $latest_order->shipping_method_id;
            $shipping_method = ShippingMethod::find($shipping_method_id);
            if ($shipping_method) {
                $shipping_method_name = $shipping_method->name;
            }
        }

        if ($this->session_id) {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $session_info = Session::retrieve($this->session_id);

            if ($session_info->payment_status !== 'paid') {
                $latest_order->payment_status = 'failed';
                $latest_order->save();
                return redirect()->route('cancel');
            } else if ($session_info->payment_status === 'paid') {
                $latest_order->payment_status = 'paid';
                $latest_order->save();
            }
        }

        return view('livewire.success-page', [
            'shipping_method_cost' => $shipping_method_cost,
            'order' => $latest_order,
            'shipping_method_name' => $shipping_method_name,
        ]);
    }
}
