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
        $league = League::inRandomOrder()->first();

        $apiUrl = "https://google-news13.p.rapidapi.com/sport?lr={$league->code}";

        $response = Http::withHeaders([
            'x-rapidapi-host' => 'google-news13.p.rapidapi.com',
            'x-rapidapi-key' => env('EXTERNAL_API_KEY'),
        ])->get($apiUrl);

        if ($response->successful()) {
            $articles = $response->json();

            if (isset($articles['items'])) {
                foreach ($articles['items'] as $article) {
                    try {
                        $rewrittenContent = $this->openAiService->write($article['title'], $article['snippet']);

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
