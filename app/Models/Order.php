<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'purchase_date',
    ];

    protected $casts = [
        'purchase_date' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function medications()
    {
        return $this->belongsToMany(Medication::class, 'order_items')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }
}
