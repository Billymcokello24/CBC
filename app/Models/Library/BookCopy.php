<?php

namespace App\Models\Library;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookCopy extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'copy_number',
        'barcode',
        'condition',
        'status',
        'acquisition_date',
        'acquisition_source',
        'notes',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function loans()
    {
        return $this->hasMany(BookLoan::class);
    }
}
