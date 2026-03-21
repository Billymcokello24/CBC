<?php

namespace App\Models\Finance;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'description',
        'is_mandatory',
        'is_recurring',
        'recurrence_period',
        'is_active',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function invoices()
    {
        return $this->hasManyThrough(Invoice::class, InvoiceItem::class, 'fee_category_id', 'id', 'id', 'invoice_id');
    }
}
