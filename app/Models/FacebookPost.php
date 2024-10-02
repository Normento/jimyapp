<?php

namespace App\Models;

use App\Models\FacebookPage;
use App\Models\RewrittenArticle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FacebookPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'rewritten_article_id',
        'facebook_post_id',
        'status',
        'scheduled_at',
        'posted_at',
    ];

    /**
     * Obtenir l'article réécrit associé à la publication Facebook.
     */
    public function rewrittenArticle()
    {
        return $this->belongsTo(RewrittenArticle::class);
    }


    public function facebookPage()
    {
        return $this->belongsTo(FacebookPage::class);
    }
}
