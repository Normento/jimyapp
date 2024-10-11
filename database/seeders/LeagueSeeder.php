<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\League;

class LeagueSeeder extends Seeder
{
    public function run()
    {
        $leagues = [
            // Les 5 grands championnats européens
            [
                'name' => 'Premier League',
                'code' => 'en-GB', // Angleterre
            ],
            [
                'name' => 'La Liga',
                'code' => 'es-ES', // Espagne
            ],
            [
                'name' => 'Bundesliga',
                'code' => 'de-DE', // Allemagne
            ],
            [
                'name' => 'Serie A',
                'code' => 'it-IT', // Italie
            ],
            [
                'name' => 'Ligue 1',
                'code' => 'fr-FR', // France
            ],
            // Ligues africaines
            [
                'name' => 'Ligue 1 Pro (Maroc)',
                'code' => 'fr-MA', // Maroc
            ],
            [
                'name' => 'Premier League (Égypte)',
                'code' => 'ar-EG', // Égypte
            ],
            [
                'name' => 'Premier League (Nigeria)',
                'code' => 'en-NG', // Nigeria
            ],
            [
                'name' => 'Premier Soccer League (Afrique du Sud)',
                'code' => 'en-ZA', // Afrique du Sud
            ],
            [
                'name' => 'Ligue 1 (Sénégal)',
                'code' => 'fr-SN', // Sénégal
            ],
            [
                'name' => 'Ligue 1 (Côte d’Ivoire)',
                'code' => 'fr-CI', // Côte d’Ivoire
            ],
            [
                'name' => 'Premier League (Kenya)',
                'code' => 'en-KE', // Kenya
            ],
            [
                'name' => 'Premier League (Ghana)',
                'code' => 'en-GH', // Ghana
            ],
            [
                'name' => 'Ligue 1 (Mali)',
                'code' => 'fr-ML', // Mali
            ],
            [
                'name' => 'Ligue 1 (Cameroun)',
                'code' => 'fr-CM', // Cameroun
            ],
            [
                'name' => 'Ligue 1 (Burkina Faso)',
                'code' => 'fr-BF', // Burkina Faso
            ],
            [
                'name' => 'Ligue 1 (Togo)',
                'code' => 'fr-TG', // Togo
            ],
            [
                'name' => 'Ligue 1 (Gabon)',
                'code' => 'fr-GA', // Gabon
            ],
            [
                'name' => 'Ligue 1 (Bénin)',
                'code' => 'fr-BJ', // Bénin
            ],
            [
                'name' => 'Premier League (Ouganda)',
                'code' => 'en-UG', // Ouganda
            ],
            [
                'name' => 'Premier League (Tanzanie)',
                'code' => 'en-TZ', // Tanzanie
            ],
            [
                'name' => 'Premier League (Zambie)',
                'code' => 'en-ZM', // Zambie
            ],
            [
                'name' => 'Premier League (Zimbabwe)',
                'code' => 'en-ZW', // Zimbabwe
            ],
            [
                'name' => 'Premier League (Namibie)',
                'code' => 'en-NA', // Namibie
            ],
        ];

        foreach ($leagues as $league) {
            League::create($league);
        }
    }
}
