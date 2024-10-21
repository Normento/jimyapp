<?php

namespace App\Jobs;

use App\Models\RewrittenArticle;
use App\Models\FacebookPage;
use App\Models\FacebookPost;
use App\Services\FacebookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PublishArticleToFacebookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $articleId;
    protected $facebookPageId;

    public function __construct($articleId, $facebookPageId)
    {
        $this->articleId = $articleId;
        $this->facebookPageId = $facebookPageId;
    }

    public function handle()
    {
        // Récupérer l'article
        $article = RewrittenArticle::find($this->articleId);

        if (!$article) {
            Log::error('Article non trouvé : ID ' . $this->articleId);
            return;
        }

        // Récupérer la page Facebook
        $facebookPage = FacebookPage::find($this->facebookPageId);

        if (!$facebookPage) {
            Log::error('Page Facebook non trouvée : ID ' . $this->facebookPageId);
            return;
        }

        // Initialiser le service Facebook
        $facebookService = new FacebookService(new \Facebook\Facebook([
            'app_id' => env('FACEBOOK_APP_ID'),
            'app_secret' => env('FACEBOOK_APP_SECRET'),
            'default_graph_version' => 'v12.0',
        ]));

        $facebookService->setPageAccessToken($facebookPage->access_token);
        $facebookService->setPageId($facebookPage->facebook_page_id);

        // Publier l'article
        try {
            $message = $article->title . "\n\n" . $article->content;
            $imageUrl = $article->image_url;

            $id = $facebookService->publishToPage($message, $imageUrl);

            if ($id) {
                $data =  [
                    'facebook_post_id' => $id,
                    'rewritten_article_id' => $article->id,
                    'status' => 'posted',
                    'posted_at' => Carbon::now(),
                ];

                FacebookPost::create($data);
                
            }

            Log::info('Article ID ' . $article->id . ' publié sur Facebook.');
        } catch (\Exception $e) {
            Log::error('Échec de la publication de l\'article ID ' . $article->id . ' : ' . $e->getMessage());
        }
    }
}
