<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableData extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_request_id',
        'table_index',
        'row_index',
        'label',
        'numeric_value',
        'numeric_column',
        'raw_label_value',
        'raw_numeric_value',
        'full_row_data',
    ];

    protected $casts = [
        'full_row_data' => 'array',
        'numeric_value' => 'float',
    ];

    public function pageRequest()
    {
        return $this->belongsTo(PageRequest::class);
    }
}
