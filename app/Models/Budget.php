<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'total_budget',
        'remaining_budget',
        'period_month',
        'period_year',
    ];

    protected $casts = [
        'total_budget' => 'decimal:2',
        'remaining_budget' => 'decimal:2',
        'period_month' => 'integer',
        'period_year' => 'integer',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function budgetLogs(): HasMany
    {
        return $this->hasMany(BudgetLog::class);
    }
}