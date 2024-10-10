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
        $this->pageAccessToken = env('FACEBOOK_PAGE_ACCESS_TOKEN');
        $this->pageId = env('FACEBOOK_PAGE_ID');
    }

    public function publishToPage(string $message, string $link = null)
    {
        $data = [
            'message' => $message,
        ];

        if ($link) {
            $data['link'] = $link;
        }

        try {
            $response = $this->fb->post("/{$this->pageId}/feed", $data, $this->pageAccessToken);
            return $response->getDecodedBody();
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            // Erreur renvoyée par l'API Graph
            throw new \Exception('Graph API returned an error: ' . $e->getMessage());
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            // Erreur du SDK Facebook
            throw new \Exception('Facebook SDK returned an error: ' . $e->getMessage());
        }
    }
}
