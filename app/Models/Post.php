<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $table = 'posts';

    protected $fillable = [
        'faTitle',
        'faNickname',
        'faSummary',
        'faContent',
        'view_status',
        'date_status',
        'since_time',
        'until_time',
        'view_count',
        'iconExtension',
        'top_status',
        'special_status',
    ];
}
