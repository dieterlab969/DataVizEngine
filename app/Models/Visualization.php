<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visualization extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_request_id',
        'table_index',
        'numeric_column',
        'label_column',
        'labels',
        'values',
        'title',
    ];

    protected $casts = [
        'labels' => 'array',
        'values' => 'array',
    ];

    public function pageRequest()
    {
        return $this->belongsTo(PageRequest::class);
    }
}
