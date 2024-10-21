<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\FacebookPage;
use Illuminate\Bus\Queueable;
use App\Models\RewrittenArticle;
use App\Models\PublicationConfig;
use App\Services\FacebookService;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

use App\Jobs\PublishArticleToFacebookJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PublishArticlesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle()
    {
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
        $articles = RewrittenArticle::where('status', 'published')
            ->where('status', 'processed')
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

        // Étape 4 : Initialiser le service Facebook
        $facebookService = new FacebookService(new \Facebook\Facebook([
            'app_id' => env('FACEBOOK_APP_ID'),
            'app_secret' => env('FACEBOOK_APP_SECRET'),
            'default_graph_version' => 'v12.0',
        ]));

        $facebookService->setPageAccessToken($facebookPage->access_token);
        $facebookService->setPageId($facebookPage->facebook_page_id);

        // Étape 5 : Programmer la publication des articles
        foreach ($articles as $index => $article) {
            $delay = now()->addMinutes($publicationConfig->interval_minutes * $index);

            // Dispatcher un job pour chaque article
            PublishArticleToFacebookJob::dispatch($article->id, $facebookPage->id)
                ->delay($delay);
        }
    }
}
