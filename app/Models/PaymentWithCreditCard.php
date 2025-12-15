<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentWithCreditCard extends Model
{
    protected $table = 'payment_with_credit_card';

    protected $fillable = [
        'payment_id',
        'customer_id',
        'payhere_payment_id',
        'payhere_order_id',
        'card_holder_name',
        'card_no',
        'card_expiry',
        'payment_method',
    ];

    protected $casts = [
        'payment_id' => 'integer',
        'customer_id' => 'integer',
    ];

    public function payment()
    {
        return $this->belongsTo(PaymentSale::class, 'payment_id');
    }

    public function customer()
    {
        return $this->belongsTo(Client::class, 'customer_id');
    }
}
