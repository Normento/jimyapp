<?php

namespace App\Http\Controllers\Auth;

use App\Models\FacebookPage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;

class FacebookController extends Controller
{
    /**
     * Redirige l'utilisateur vers la page de connexion Facebook.
     *
     */
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')
            ->scopes(['pages_manage_posts', 'pages_read_engagement', 'pages_show_list']) // Permissions nécessaires
            ->redirect();
    }

    /**
     * Gère le callback après l'authentification Facebook.
     *
     */
    public function handleFacebookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->stateless()->user();

            // Récupérer le token utilisateur
            $accessToken = $facebookUser->token;
            $userId = $facebookUser->getId();

            // Obtenir les pages que l'utilisateur gère
            $response = Http::get("https://graph.facebook.com/{$userId}/accounts", [
                'access_token' => $accessToken,
            ]);

            if ($response->successful()) {
                $pages = $response->json()['data'];

                foreach ($pages as $page) {
                    FacebookPage::updateOrCreate(
                        ['facebook_page_id' => $page['id']],
                        [
                            'user_id' => Auth::id(), // Associez à l'utilisateur connecté
                            'name' => $page['name'],
                            'access_token' => $page['access_token'],
                            'perms' => implode(',', $page['perms'] ?? []),
                        ]
                    );
                }

                return redirect()->route('filament.admin.resources.facebook-posts.index')->with('success', 'Page(s) Facebook connectée(s) avec succès.');
            } else {
                return redirect()->route('filament.admin.resources.facebook-posts.index')->with('error', 'Échec de récupération des pages Facebook.');
            }
        } catch (\Exception $e) {
            return redirect()->route('filament.admin.resources.facebook-posts.index')->with('error', 'Erreur lors de la connexion avec Facebook.');
        }
    }
}
