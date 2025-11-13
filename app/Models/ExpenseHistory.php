<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseHistory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'date',
        'branch_id',
        'expense_id',
        'nominal',
        'description',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'date' => 'date',
        'nominal' => 'decimal:2',
    ];

    /**
     * The branch that this expense belongs to.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * The expense definition (category) for this history entry.
     */
    public function expense()
    {
        return $this->belongsTo(Expense::class, 'expense_id');
    }
}
