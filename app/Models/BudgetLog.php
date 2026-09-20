<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'budget_id',
        'transaction_id',
        'previous_balance',
        'current_balance',
        'amount',
        'type',
        'created_at',
    ];

    protected $casts = [
        'previous_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}