<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visualization extends Model
{
    use HasFactory;

    protected $fillable = ['page_request_id', 'file_path'];

    public function pageRequest()
    {
        return $this->belongsTo(PageRequest::class);
    }
}
