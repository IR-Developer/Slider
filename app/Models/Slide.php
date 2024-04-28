<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    use HasFactory;

    protected $table = 'slides';

    protected $fillable = [
        'title',
        'href',
        'blank',
        'slideExtension',
        'slider_id',
        'status',
    ];

    public function slider()
    {
        return $this->belongsTo('App\Models\Slider');
    }
}
