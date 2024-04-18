<?php

namespace App\Livewire;

use App\Models\Order;
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
        $latest_order =Order::with('address')->where('user_id',auth()->user()->id)->latest()->first();
        if($this->session_id){
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $session_info =Session::retrieve($this->session_id);

            if($session_info->payment_status !== 'paid'){
                $latest_order->payment_status = 'failed';
                $latest_order->save();
                return redirect()->route('cancel');

            }else if($session_info->payment_status === 'paid'){
                    $latest_order->payment_status = 'paid';
                    $latest_order->save();
            }
        }

        // Check if the shipping method is J&T or other specific shipping methods
        if($latest_order->shipping_method_id == 1){
            $latest_order->shipping_method_id = 'J&T';
        } else if($latest_order->shipping_method_id == 2){
            $latest_order->shipping_method_id = 'Other Shipping Method 1';
        } else if($latest_order->shipping_method_id == 3){
            $latest_order->shipping_method_id = 'Other Shipping Method 2';
        }

        return view('livewire.success-page' ,[
            'order' => $latest_order,
        ]);
    }
}
