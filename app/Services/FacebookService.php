<?php

namespace App\Services;

use Facebook\Facebook;
use Illuminate\Support\Facades\Log;

class FacebookService
{
    protected $fb;
    protected $pageAccessToken;
    protected $pageId;

    public function __construct(Facebook $fb)
    {
        $this->fb = $fb;
    }

    public function setPageAccessToken($token)
    {
        $this->pageAccessToken = $token;
    }

    public function setPageId($pageId)
    {
        $this->pageId = $pageId;
    }

    public function publishToPage(string $message, string $imageUrl = null)
    {
        try {
            // Vérifier si une image est fournie
            if ($imageUrl) {
                // Étape 1 : Télécharger l'image en tant que média non publié
                $mediaResponse = $this->fb->post("/{$this->pageId}/photos", [
                    'url' => $imageUrl,
                    'published' => false,
                ], $this->pageAccessToken);

                $mediaBody = $mediaResponse->getDecodedBody();

                Log::info("Media", $mediaBody);

                if (!isset($mediaBody['id'])) {
                    throw new \Exception('Échec du téléchargement du média : ' . json_encode($mediaBody));
                }

                if (isset($mediaBody['error'])) {
                    throw new \Exception('Erreur du média : ' . $mediaBody['error']['message']);
                }
                $mediaId = $mediaBody['id'];

                // Préparer les données de la publication avec l'image
                $data = [
                    'message' => $message,
                    'object_attachment' => $mediaId,
                ];
            } else {
                // Préparer les données de la publication sans image
                $data = [
                    'message' => $message,
                ];
            }

            Log::info("DATA", $data);

            // Publier le message
            $response = $this->fb->post("/{$this->pageId}/feed", $data, $this->pageAccessToken);

            $result = $response->getDecodedBody();

            Log::info("RESULT", $result);

            $postId = $result['id']; // L'ID de la publication
            return $postId;

        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            // Erreur renvoyée par l'API Graph
            throw new \Exception('Graph API returned an error: ' . $e->getMessage(). ' - ' . $e->getFile());
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            // Erreur du SDK Facebook
            throw new \Exception('Facebook SDK returned an error: ' . $e->getMessage(). ' - ' . $e->getFile());
        }
    }

}
