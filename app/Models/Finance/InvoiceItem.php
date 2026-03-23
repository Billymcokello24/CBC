<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class InvoiceItem extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'invoice_id',
        'fee_category_id',
        'description',
        'quantity',
        'unit_amount',
        'subtotal',
        'tax',
        'total',
    ];

    protected $casts = [
        'unit_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function feeCategory()
    {
        return $this->belongsTo(FeeCategory::class);
    }
}
