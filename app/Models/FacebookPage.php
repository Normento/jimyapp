<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FacebookPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'facebook_page_id',
        'name',
        'access_token',
        'perms',
    ];

    protected $casts = [
        'access_token' => 'encrypted', // Chiffre automatiquement le token
    ];

    /**
     * Obtenir l'utilisateur associé à la page Facebook.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
        
    }
}
