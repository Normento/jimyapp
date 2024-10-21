<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use App\Models\League;
use App\Models\FacebookPage;
use Illuminate\Http\Request;
use App\Services\OpenAiService;
use App\Models\RewrittenArticle;
use App\Models\PublicationConfig;
use App\Services\FacebookService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use App\Jobs\PublishArticleToFacebookJob;

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

                return redirect()->route('filament.admin.resources.facebook-pages.index')->with('success', 'Page(s) Facebook connectée(s) avec succès.');
            } else {
                return redirect()->route('filament.admin.resources.facebook-pages.index')->with('error', 'Échec de récupération des pages Facebook.');
            }
        } catch (\Exception $e) {
            return redirect()->route('filament.admin.resources.facebook-pages.index')->with('error', 'Erreur lors de la connexion avec Facebook.');
        }
    }



    public function post()
    {
        //$league = League::inRandomOrder()->first();

        $league = League::where('code', 'fr-FR')->first();

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
                        //$rewrittenContent = $this->openAiService->write($article['title'], $article['snippet']);
                        //Log::info('CONTENT'.$rewrittenContent);
                       // dd($rewrittenContent);


                        RewrittenArticle::create([
                            'league_id'   => $league->id,
                            'title'       => $article['title'],
                            'description' => $article['snippet'],
                            'content'     => $article['snippet'],
                            'url'         => $article['newsUrl'],
                            'image_url'   => $article['images']['thumbnail'] ?? null,
                            'status'      => 'processed',
                        ]);

                        if ($article['hasSubnews'] == true) {
                            foreach ($article['subnews'] as $subnews) {
                                try {
                                    //$rewrittenSubnewsContent = $this->openAiService->write($subnews['title'], $subnews['snippet']);

                                    RewrittenArticle::create([
                                        'league_id'   => $league->id,
                                        'title'       => $subnews['title'],
                                        'description' => $subnews['snippet'],
                                        'content'     => $subnews['snippet'],
                                        'url'         => $subnews['newsUrl'],
                                        'image_url'   => $subnews['images']['thumbnail']?? null,
                                        'status'      => 'processed',
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


    public function publish(){
        // Étape 1 : Récupérer une configuration de publication active
        $publicationConfig = PublicationConfig::where('is_active', true)
        ->whereDate('start_date', '<=', Carbon::today())
        ->whereDate('end_date', '>=', Carbon::today())
        ->first();

        if (!$publicationConfig) {
            Log::info('Aucune configuration de publication active trouvée.');
            return;
        }

        // Étape 2 : Récupérer les articles réécrits avec le statut 'published'
        $articles = RewrittenArticle::where('status', 'processed')
            ->take($publicationConfig->number_of_posts_per_day)
            ->get();

        if ($articles->isEmpty()) {
            Log::info('Aucun article publié trouvé.');
            return;
        }

        // Étape 3 : Récupérer les informations de la page Facebook
        $facebookPage = FacebookPage::where('id', $publicationConfig->page_id)->first();

        if (!$facebookPage) {
            Log::error('Aucune page Facebook trouvée pour l\'utilisateur ID : ' . $publicationConfig->user_id);
            return;
        }

        /* // Étape 4 : Initialiser le service Facebook
        $facebookService = new FacebookService(new \Facebook\Facebook([
            'app_id' => env('FACEBOOK_APP_ID'),
            'app_secret' => env('FACEBOOK_APP_SECRET'),
            'default_graph_version' => 'v12.0',
        ]));

        $facebookService->setPageAccessToken($facebookPage->access_token);
        $facebookService->setPageId($facebookPage->facebook_page_id); */

        // Étape 5 : Programmer la publication des articles
        foreach ($articles as $index => $article) {
            $delay = now()->addMinutes($publicationConfig->interval_minutes * $index);

            // Dispatcher un job pour chaque article
            PublishArticleToFacebookJob::dispatch($article->id, $facebookPage->id)
                ->delay($delay);
        }

        return true;
    }
}
