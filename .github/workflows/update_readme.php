<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;

$username = 'polodu13160';
$readmeTemplate = file_get_contents('README.md');

function fetchGitHubData($username) {
    $client = new Client();
    $oneWeekAgo = new DateTime('-1 week');
    $firstOfMonth = new DateTime('first day of this month');
    $today = new DateTime();

    // Echo the dates for debugging purposes
    echo "Date one week ago: " . $oneWeekAgo->format('Y-m-d') . "\n";
    echo "Date first of month: " . $firstOfMonth->format('Y-m-d') . "\n";

    $commitsWeekCount = 0;
    $commitsMonthCount = 0;

    // Loop for the week
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($oneWeekAgo, $interval, $today->add(new DateInterval('P1D')));

    foreach ($period as $date) {
        $dateString = $date->format('Y-m-d');
        $response = $client->get("https://api.github.com/search/commits?q=author:$username+committer-date:$dateString", [
            'headers' => ['Accept' => 'application/vnd.github.v3+json'],
        ]);
        $data = json_decode($response->getBody(), true);
        $commitsWeekCount += $data['total_count'];
    }

    // Loop for the month
    $period = new DatePeriod($firstOfMonth, $interval, $today);

    foreach ($period as $date) {
        $dateString = $date->format('Y-m-d');
        $response = $client->get("https://api.github.com/search/commits?q=author:$username+committer-date:$dateString", [
            'headers' => ['Accept' => 'application/vnd.github.v3+json'],
        ]);
        $data = json_decode($response->getBody(), true);
        $commitsMonthCount += $data['total_count'];
    }

    $reposResponse = $client->get("https://api.github.com/users/$username/repos?sort=created&per_page=3");
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

fetchGitHubData($username);
