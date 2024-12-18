<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;

$username = 'polodu13160';
$token = getenv('GH_TOKEN'); // Assurez-vous que cette variable d'environnement est définie avec votre token GitHub
$readmeTemplate = file_get_contents('README.md');

function fetchGitHubData($username, $token) {
    $client = new Client();
    $headers = [
        'Accept' => 'application/vnd.github.v3+json',
        'Authorization' => "token $token"
    ];
    $oneWeekAgo = new DateTime('-1 week');
    $firstOfMonth = new DateTime('first day of this month');
    $today = new DateTime();

    echo "Date one week ago: " . $oneWeekAgo->format('Y-m-d') . "\n";
    echo "Date first of month: " . $firstOfMonth->format('Y-m-d') . "\n";

    $commitsWeekCount = 0;
    $commitsMonthCount = 0;

    $interval = new DateInterval('P1D');
    $period = new DatePeriod($oneWeekAgo, $interval, $today->add(new DateInterval('P1D')));

    // Boucle pour la semaine
    foreach ($period as $date) {
        $dateString = $date->format('Y-m-d');
        $response = $client->get("https://api.github.com/search/commits?q=author:$username+committer-date:$dateString", [
            'headers' => $headers,
        ]);
        $data = json_decode($response->getBody(), true);
        $commitsWeekCount += $data['total_count'];
        sleep(1); // Ajout d'un délai d'une seconde pour éviter de dépasser la limite
    }

    $period = new DatePeriod($firstOfMonth, $interval, $today);

    // Boucle pour le mois
    foreach ($period as $date) {
        $dateString = $date->format('Y-m-d');
        $response = $client->get("https://api.github.com/search/commits?q=author:$username+committer-date:$dateString", [
            'headers' => $headers,
        ]);
        $data = json_decode($response->getBody(), true);
        $commitsMonthCount += $data['total_count'];
        sleep(1); // Ajout d'un délai d'une seconde pour éviter de dépasser la limite
    }

    $reposResponse = $client->get("https://api.github.com/users/$username/repos?sort=created&per_page=3", [
        'headers' => $headers,
    ]);
    $repos = json_decode($reposResponse->getBody(), true);

    $recentProjects = '';
    foreach ($repos as $repo) {
        $recentProjects .= "| 🟢 **{$repo['name']}** | [Accéder au repo]({$repo['html_url']}) |\n";
    }

    global $readmeTemplate;
    $updatedReadme = str_replace([
        '{{ commits_week }}',
        '{{ commits_month }}',
        '{{ recent_projects }}',
        '{{ github_username }}',
        '{{ last_update }}'
    ], [
        $commitsWeekCount,
        $commitsMonthCount,
        $recentProjects,
        $username,
        date('Y-m-d H:i:s')
    ], $readmeTemplate);

    file_put_contents('README.md', $updatedReadme);
}

fetchGitHubData($username, $token);
