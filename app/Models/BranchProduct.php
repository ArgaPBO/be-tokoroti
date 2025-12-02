<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BranchProduct extends Model
{
    //
    use SoftDeletes;
    protected $fillable = ['name','branch_price','branch_id','product_id'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function productHistories()
{
    return $this->hasMany(ProductHistory::class, 'product_id', 'product_id')
                ->where('branch_id', $this->branch_id);
}

}
