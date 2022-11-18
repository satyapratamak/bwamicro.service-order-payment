<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    use HasFactory;

    protected $table = 't_payment_logs';
    protected $fillable = [
        'status', 'payment_type', 't_order_id', 'raw_response'
    ];
    protected $date = ['updated_at', 'created_at'];
    public function getCreatedAtAttribute($date)
    {
        //return  $date->format('Y-m-d H:i');
        return date('Y-m-d H:i:s', strtotime($date));
    }

    public function getUpdatedAtAttribute($date)
    {
        //return $date->format('Y-m-d H:i');
        return date('Y-m-d H:i:s', strtotime($date));
    }
}
