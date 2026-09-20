<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'budget_id' => $this->budget_id,
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
                'type' => $this->category?->type,
            ],
            'item_name' => $this->item_name,
            'quantity' => $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'total_amount' => (float) $this->total_amount,
            'type' => $this->type,
            'transaction_date' => $this->transaction_date->format('Y-m-d H:i:s'),
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'audit_log' => $this->whenLoaded('budgetLog', function () {
                return [
                    'previous_balance' => (float) $this->budgetLog->previous_balance,
                    'current_balance' => (float) $this->budgetLog->current_balance,
                    'amount' => (float) $this->budgetLog->amount,
                ];
            }),
            'budget_summary' => $this->whenLoaded('budget', function () {
                return [
                    'total_budget' => (float) $this->budget->total_budget,
                    'remaining_budget' => (float) $this->budget->remaining_budget,
                ];
            }),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}