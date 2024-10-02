<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Panel;
use App\Models\FacebookPage;
use App\Models\RewrittenArticle;
use App\Models\PublicationConfig;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];



    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    /**
     * Obtenir les pages Facebook liées à l'utilisateur.
     */
    public function facebookPages()
    {
        return $this->hasMany(FacebookPage::class);
    }

    /**
     * Obtenir les configurations de publication de l'utilisateur.
     */
    public function publicationConfigs()
    {
        return $this->hasMany(PublicationConfig::class);
    }

    /**
     * Obtenir les articles réécrits de l'utilisateur.
     */
    public function rewrittenArticles()
    {
        return $this->hasMany(RewrittenArticle::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
