<?php

namespace App\Models\Library;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class BookCategory extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function books()
    {
        return $this->hasMany(Book::class);
    }
}
