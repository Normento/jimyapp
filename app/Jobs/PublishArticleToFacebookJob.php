<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\FacebookPage;
use App\Models\FacebookPost;
use App\Models\RewrittenArticle;
use App\Services\FacebookService;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

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
            Log::error('Article non trouvé pour l\'ID : ' . $this->articleId);
            return;
        }

        // Récupérer la page Facebook
        $facebookPage = FacebookPage::find($this->facebookPageId);

        if (!$facebookPage) {
            Log::error('Page Facebook non trouvée pour l\'ID : ' . $this->facebookPageId);
            return;
        }

        $facebookService = new FacebookService();

        $pub = "Code promo 1xbet : BOURSE";

        // Publier l'article sur la page Facebook
        try {
            $message = $article->title . "\n\n" . $article->description . "\n\n" .$pub;
            $imageUrl = $article->image_url; // URL de l'image associée à l'article, si disponible

            // Appel à l'API via le service Facebook
            $response = $facebookService->publishToPage(
                $message,
                $imageUrl,
                $facebookPage->facebook_page_id,
                $facebookPage->access_token
            );


            // Sauvegarde de la publication dans la base de données
            $data = [
                'facebook_post_id' => $response,
                'rewritten_article_id' => $article->id,
                'status' => 'posted',
                'posted_at' => Carbon::now(),
            ];

            FacebookPost::create($data);

            $article->update(['status' => 'processed']);

            //Log::info('Article ID ' . $article->id . ' publié avec succès sur Facebook. ID de la publication Facebook : ' . $response['id']);

        } catch (\Exception $e) {
            // Gestion des exceptions et logging des erreurs détaillées
            //Log::error('Échec de la publication de l\'article ID ' . $article->id . ' : ' . $e->getMessage() . ' dans ' . $e->getFile() . ' à la ligne ' . $e->getLine());
        }
    }
}
