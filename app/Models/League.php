<?php

namespace App\Models;

use App\Models\RewrittenArticle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class League extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'code'];

    /**
     * Obtenir les articles réécrits associés à la ligue.
     */
    public function rewrittenArticles()
    {
        return $this->hasMany(RewrittenArticle::class);
    }
}
