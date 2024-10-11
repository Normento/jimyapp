<?php

namespace App\Http\Controllers\Auth;

use App\Models\League;
use App\Models\FacebookPage;
use Illuminate\Http\Request;
use App\Services\OpenAiService;
use App\Models\RewrittenArticle;
use Illuminate\Support\Facades\Log;
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

     protected $openAiService;

     public function __construct()
     {
         $this->openAiService = new OpenAiService();
     }
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



    public function post()
    {
        $league = League::inRandomOrder()->first();

        $apiUrl = "https://google-news13.p.rapidapi.com/sport?lr={$league->code}";

        $response = Http::withHeaders([
            'x-rapidapi-host' => 'google-news13.p.rapidapi.com',
            'x-rapidapi-key' => env('EXTERNAL_API_KEY'),
        ])->get($apiUrl);

        if ($response->successful()) {
            $articles = $response->json();
            //dd($articles['items']);
            //echo $articles;

            if (isset($articles['items'])) {
                foreach ($articles['items'] as $article) {

                    try {
                        $rewrittenContent = $this->openAiService->write($article['title'], $article['snippet']);
                        Log::info('CONTENT'.$rewrittenContent);
                       // dd($rewrittenContent);


                        RewrittenArticle::create([
                            'league_id'   => $league->id,
                            'title'       => $article['title'],
                            'description' => $article['snippet'],
                            'content'     => $rewrittenContent,
                            'url'         => $article['newsUrl'],
                            'image_url'   => $article['images']['thumbnail'] ?? null,
                            'status'      => 'published',
                        ]);

                        if ($article['hasSubnews'] == true) {
                            foreach ($article['subnews'] as $subnews) {
                                try {
                                    $rewrittenSubnewsContent = $this->openAiService->write($subnews['title'], $subnews['snippet']);

                                    RewrittenArticle::create([
                                        'league_id'   => $league->id,
                                        'title'       => $subnews['title'],
                                        'description' => $subnews['snippet'],
                                        'content'     => $rewrittenSubnewsContent,
                                        'url'         => $subnews['newsUrl'],
                                        'image_url'   => $subnews['images']['thumbnail']?? null,
                                        'status'      => 'published',
                                    ]);
                                } catch (\Exception $e) {
                                    Log::error('Erreur lors du traitement de la sous-nouvelle', [
                                        'error' => $e->getMessage(),
                                        'article_title' => $subnews['title'] ?? 'Titre inconnu',
                                    ]);
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('Erreur lors du traitement de l\'article', [
                            'error' => $e->getMessage(),
                            'article_title' => $article['title'] ?? 'Titre inconnu',
                        ]);
                    }
                }
            } else {
                Log::warning('Aucun article trouvé dans la réponse de l\'API', ['response' => $articles]);
            }
        } else {
            Log::error('Échec de la récupération des articles', ['response' => $response->body()]);
        }

    }
}
