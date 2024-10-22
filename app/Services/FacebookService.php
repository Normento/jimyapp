<?php

namespace App\Services;


use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class FacebookService
{
    protected $client;

    public function __construct()
    {
        // Initialisation du client HTTP
        $this->client = new Client();
    }

    /**
     * Publier un message et une image sur la page Facebook
     * @param string $message - Le texte à publier
     * @param string|null $imageUrl - L'URL de l'image à publier (facultatif)
     * @return string|null - L'ID de la publication si elle est réussie, sinon null
     */
    public function publishToPage($message, $imageUrl = null,$pageId,$pageAccessToken)
    {
        try {
            // URL de l'API Graph pour publier sur le feed de la page
            $url = "https://graph.facebook.com/{$pageId}/feed";

            // Préparation des données à envoyer
            $data = [
                'message' => $message,
                'access_token' => $pageAccessToken, // Token d'accès à la page
            ];

            // Si une image est fournie, on fait une autre requête pour l'uploader d'abord
            if ($imageUrl) {
                // Étape 1 : Télécharger l'image en tant que média non publié
                $uploadUrl = "https://graph.facebook.com/{$pageId}/photos";
                $uploadResponse = $this->client->post($uploadUrl, [
                    'form_params' => [
                        'url' => $imageUrl,
                        'published' => false, // On ne la publie pas immédiatement
                        'access_token' => $pageAccessToken,
                    ],
                ]);

                $uploadResult = json_decode($uploadResponse->getBody(), true);

                // Récupérer l'ID du média téléchargé
                if (!isset($uploadResult['id'])) {
                    throw new \Exception('Erreur lors de l\'upload de l\'image');
                }

                $mediaId = $uploadResult['id'];

                // Ajout du média téléchargé à la publication
                $data['attached_media'] = json_encode([['media_fbid' => $mediaId]]);
            }

            // Étape 2 : Publier le message avec (ou sans) l'image
            $response = $this->client->post($url, [
                'form_params' => $data,
            ]);

            $result = json_decode($response->getBody(), true);

            if (isset($result['id'])) {
                Log::info('Publication réussie avec l\'ID : ' . $result['id']);
                return $result['id'];
            }

            throw new \Exception('Erreur lors de la publication sur Facebook');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la publication sur Facebook : ' . $e->getMessage());
            return null;
        }
    }
}
