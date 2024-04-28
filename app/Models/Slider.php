<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;

    protected $table = 'sliders';

    protected $fillable = [
        'id',
        'title',
        'width',
        'height',
        'backgroundColor',
        'autoSliding',
        'nextPrevStatus',
        'dotStatus',
        'titleStatus',
        'slidingSpeed',
        'status',
    ];

    public function slides()
    {
        return $this->hasMany('App\Models\Slide');
    }
}
