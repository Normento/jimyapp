<?php

namespace App\Services;

use Facebook\Facebook;

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
                $mediaId = $mediaBody['id'];

                // Préparer les données de la publication avec l'image
                $data = [
                    'message' => $message,
                    'attached_media' => [
                        [
                            'media_fbid' => $mediaId,
                        ],
                    ],
                ];
            } else {
                // Préparer les données de la publication sans image
                $data = [
                    'message' => $message,
                ];
            }

            // Publier le message
            $response = $this->fb->post("/{$this->pageId}/feed", $data, $this->pageAccessToken);

            $result = $response->getDecodedBody();
            $postId = $result['id']; // L'ID de la publication
            return $postId;

        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            // Erreur renvoyée par l'API Graph
            throw new \Exception('Graph API returned an error: ' . $e->getMessage());
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            // Erreur du SDK Facebook
            throw new \Exception('Facebook SDK returned an error: ' . $e->getMessage());
        }
    }

}
