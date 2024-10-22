<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\FacebookPage;
use App\Models\FacebookPost;
use Illuminate\Bus\Queueable;
use App\Models\RewrittenArticle;
use App\Services\FacebookService;
use Illuminate\Support\Facades\Log;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use NazmulHasan\LaravelFacebookPost\Facades\FacebookPost as PublishPost;

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

        $facebookService = new FacebookService();



        // // Initialiser le service Facebook
        // $facebookService = new FacebookService(new \Facebook\Facebook([
        //     'app_id' => env('FACEBOOK_APP_ID'),
        //     'app_secret' => env('FACEBOOK_APP_SECRET'),
        //     'default_graph_version' => 'v12.0',
        // ]));

        // $facebookService->setPageAccessToken($facebookPage->access_token);
        // $facebookService->setPageId($facebookPage->facebook_page_id);

        // Publier l'article
        try {
            $message = $article->title . "\n\n" . $article->content;
            $imageUrl = $article->image_url;

            $response = $facebookService->publishToPage($message,$imageUrl,$facebookPage->facebook_page_id,$facebookPage->access_token);

            //$response = PublishPost::storePostWithPhoto($imageUrl, $message);

            //Log::info('RESPONSE',$response);

            if (!$response['id']) {
                Log::error('Échec de la publication de l\'article ID ' . $article->id);
                return;
            }

            //$id = $facebookService->publishToPage($message, $imageUrl);

            if ($response['id']) {
                $data =  [
                    'facebook_post_id' => $response['id'],
                    'rewritten_article_id' => $article->id,
                    'status' => 'posted',
                    'posted_at' => Carbon::now(),
                ];

                FacebookPost::create($data);

            }

            Log::info('Article ID ' . $article->id . ' publié sur Facebook.');
        } catch (\Exception $e) {
            Log::error('Échec de la publication de l\'article ID ' . $article->id . ' : ' . $e->getMessage(). ' - ' . $e->getFile(). ' - ' . $e->getLine());
        }
    }
}
