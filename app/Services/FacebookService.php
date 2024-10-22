<?php

use Facebook\Facebook;
use Illuminate\Support\Facades\Log;

class FacebookService
{
    protected $fb;

    public function __construct()
    {
        // Initialisation du SDK Facebook avec les informations de votre app
        $this->fb = new Facebook([
            'app_id' => env('FACEBOOK_APP_ID'),
            'app_secret' => env('FACEBOOK_APP_SECRET'),
            'default_graph_version' => 'v12.0',
        ]);

    }

    public function publishToPage($message, $imageUrl = null, $pageId, $pageAccessToken)
    {
        try {

            // Si une image est fournie, nous la publions avec le message
            if ($imageUrl) {
                // Étape 1 : Télécharger l'image sur la page Facebook
                $mediaResponse = $this->fb->post("/{$pageId}/photos", [
                    'url' => $imageUrl,
                    'published' => false, // Charger l'image sans la publier immédiatement
                ], $pageAccessToken);

                $media = $mediaResponse->getGraphNode();
                $mediaId = $media['id'];

                // Étape 2 : Publier le message avec l'image
                $data = [
                    'message' => $message,
                    'attached_media' => json_encode([['media_fbid' => $mediaId]]),
                ];
            } else {
                // Si aucune image n'est fournie, nous publions seulement le message
                $data = [
                    'message' => $message,
                ];
            }

            // Publier sur la page avec l'ID de la page
            $response = $this->fb->post("/{$pageId}/feed", $data, $pageAccessToken);
            $result = $response->getGraphNode();

            Log::info('Publication réussie avec l\'ID : ' . $result['id']);
            return $result['id'];

        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            // Gestion des erreurs retournées par l'API Graph
            Log::error('Erreur API Graph : ' . $e->getMessage());
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            // Gestion des erreurs du SDK Facebook
            Log::error('Erreur SDK Facebook : ' . $e->getMessage());
        }
    }
}
