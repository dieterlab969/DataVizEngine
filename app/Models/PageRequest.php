<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageRequest extends Model
{
    use HasFactory;

    protected $fillable = ['url', 'status', 'error_message'];

    public function tableData()
    {
        return $this->hasMany(TableData::class);
    }

    public function visualizations()
    {
        return $this->hasMany(Visualization::class);
    }
}
