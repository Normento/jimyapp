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
        if ($imageUrl) {
            $mediaResponse = $this->fb->post("/{$this->pageId}/photos", [
                'url' => $imageUrl,
                'published' => false,
            ], $this->pageAccessToken);

            $mediaBody = $mediaResponse->getDecodedBody();

            Log::info("Media", $mediaBody);

            if (!isset($mediaBody['id'])) {
                throw new \Exception('Échec du téléchargement du média : ' . json_encode($mediaBody));
            }

            $mediaId = $mediaBody['id'];

            $data = [
                'message' => $message,
                'object_attachment' => $mediaId,
            ];
        } else {
            $data = [
                'message' => $message,
            ];
        }

        Log::info("DATA", $data);

        $response = $this->fb->post("/{$this->pageId}/feed", $data, $this->pageAccessToken);

        $result = $response->getDecodedBody();

        Log::info("RESULT", $result);

        if (isset($result['error'])) {
            throw new \Exception('Erreur lors de la publication : ' . $result['error']['message']);
        }

        $postId = $result['id']; // L'ID de la publication
        return $postId;

    } catch (\Facebook\Exceptions\FacebookResponseException $e) {
        throw new \Exception('Graph API a renvoyé une erreur : ' . $e->getMessage() . ' - ' . json_encode($e->getResponse()->getDecodedBody()));
    } catch (\Facebook\Exceptions\FacebookSDKException $e) {
        throw new \Exception('Le SDK Facebook a renvoyé une erreur : ' . $e->getMessage());
    }
}


}
