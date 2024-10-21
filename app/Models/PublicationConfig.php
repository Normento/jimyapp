<?php

namespace App\Models;

use App\Models\User;
use App\Models\League;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PublicationConfig extends Model
{
    use HasFactory, SoftDeletes;



    
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->user_id = Auth::id();
        });
    }


    protected $fillable = [
        'user_id',
        'page_id',
        'number_of_posts_per_day',
        'interval_minutes',
        'start_date',
        'end_date',
        'is_active',
    ];

    /**
     * Obtenir l'utilisateur associé à la configuration de publication.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir la ligue associée à la configuration de publication.
     */
    public function league()
    {
        return $this->belongsTo(League::class);
    }

    public function page()
    {
        return $this->belongsTo(FacebookPage::class);
    }
}
