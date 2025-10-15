<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableData extends Model
{
    use HasFactory;

    protected $fillable = ['page_request_id', 'column_name', 'values'];

    protected $casts = [
        'values' => 'array',
    ];

    public function pageRequest()
    {
        return $this->belongsTo(PageRequest::class);
    }
}
