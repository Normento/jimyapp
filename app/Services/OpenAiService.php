<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class OpenAiService
{
    public function write(string $title, string $snippet)
    {
        // Récupérer le modèle de prompt depuis le fichier .env
        $promptTemplate = env('OPENAI_PROMPT_TEMPLATE');

        // Remplacement des variables {{title}} et {{snippet}} par les valeurs réelles
        $prompt = str_replace(['{{title}}', '{{snippet}}'], [$title, $snippet], $promptTemplate);

        $result = OpenAI::chat()->create([
            'model' => 'gpt-4', // On peut aussi utiliser 'gpt-3.5-turbo'
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => 500,
            'temperature' => 0.7,
        ]);

        return $result['choices'][0]['message']['content'];
    }
}