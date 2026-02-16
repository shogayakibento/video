<?php

namespace App\Http\Controllers;

use App\Services\FanzaApiService;

class HomeController extends Controller
{
    public function __invoke(FanzaApiService $api)
    {
        $categories = config('fanza.categories');

        if ($api->isConfigured()) {
            $ranking = $api->getRanking('digital', 'videoa', 10);
            $newReleases = $api->getNewReleases('digital', 'videoa', 12);
            $vrRanking = $api->getRanking('digital', 'videoc', 6);
        } else {
            $ranking = $api->getSampleItems('douga', 10);
            $newReleases = $api->getSampleItems('douga', 12);
            $vrRanking = $api->getSampleItems('vr', 6);
        }

        return view('home', [
            'categories' => $categories,
            'rankingItems' => $ranking['result']['items'] ?? [],
            'newReleaseItems' => $newReleases['result']['items'] ?? [],
            'vrItems' => $vrRanking['result']['items'] ?? [],
            'isConfigured' => $api->isConfigured(),
        ]);
    }
}
