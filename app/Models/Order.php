<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 't_orders';
    protected $fillable = [
        'status', 'user_id', 'course_id', 'metadata', 'snap_url'
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

    protected $castJSON = ['metadata'];

    public function getCastJSONToArray($castJSON)
    {
        return json_decode($castJSON, true);
    }
}
