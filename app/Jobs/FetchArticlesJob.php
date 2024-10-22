<?php

namespace App\Jobs;

use App\Models\League;
use App\Models\RewrittenArticle;
use App\Services\OpenAiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchArticlesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected $openAiService;

    public function __construct()
    {
        $this->openAiService = new OpenAiService();
    }

    public function handle()
    {
        //$league = League::inRandomOrder()->first();
        //$apikey = env('API_KEY');

        //$apiUrl = "https://google-news13.p.rapidapi.com/sport?lr={$league->code}";
        $apiUrl = "https://newsapi.org/v2/top-headlines/?sources=lequipe";

        $response = Http::withHeaders([
            'x-api-key' => env('API_KEY'),
        ])->get($apiUrl);

        if ($response->successful()) {
            $articles = $response->json();

            if (isset($articles['articles'])) {
                foreach ($articles['articles'] as $article) {
                    try {
                       // $rewrittenContent = $this->openAiService->write($article['title'], $article['snippet']);

                        RewrittenArticle::create([
                            'league_id'   => 5,
                            'title'       => $article['title'],
                            'description' => $article['description'],
                            'content'     => $article['content'],
                            'url'         => $article['url'],
                            'image_url'   => $article['urlToImage'] ?? null,
                            'status'      => 'pending',
                        ]);

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
