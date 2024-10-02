<?php

namespace App\Models;

use App\Models\User;
use App\Models\League;
use App\Models\FacebookPost;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RewrittenArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'league_id',
        'user_id',
        'title',
        'description',
        'content',
        'url',
        'image_url',
        'status',
    ];

    /**
     * Obtenir la ligue associée à l'article réécrit.
     */
    public function league()
    {
        return $this->belongsTo(League::class);
    }

    /**
     * Obtenir l'utilisateur associé à l'article réécrit.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir les publications Facebook liées à l'article réécrit.
     */
    public function facebookPosts()
    {
        return $this->hasMany(FacebookPost::class);
    }
}
