<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductHistory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'date',
        'branch_id',
        'product_id',
        'quantity',
        'product_price',
        'discount_percent',
        'discount_price',
        'transaction_type',
        'shift',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'date' => 'date',
        'quantity' => 'integer',
        'product_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_price' => 'decimal:2',
    ];

    /**
     * Get the branch that owns this history record.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Get the product that this history record refers to.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
