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

        $apiUrl = "https://google-news13.p.rapidapi.com/sport?league={$league->code}";

        $response = Http::withHeaders([
            'x-rapidapi-host' => 'google-news13.p.rapidapi.com',
            'x-rapidapi-key' => env('RAPIDAPI_KEY'),
        ])->get($apiUrl);

        if ($response->successful()) {
            $articles = $response->json();

            if (isset($articles['data'])) {
                foreach ($articles['data'] as $article) {
                    try {
                        $rewrittenContent = $this->openAiService->write($article['title'], $article['snippet']);

                        RewrittenArticle::create([
                            'league_id'   => $league->id,
                            'user_id'     => null,
                            'title'       => $article['title'],
                            'description' => $article['snippet'],
                            'content'     => $rewrittenContent,
                            'url'         => $article['url'],
                            'image_url'   => $article['image_url'] ?? null,
                            'status'      => 'published',
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

        self::dispatch()->delay(now()->addMinutes(15));
    }
}
