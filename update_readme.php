<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;

$username = 'polodu13160';
$readmeTemplate = file_get_contents('README.md');

function fetchGitHubData($username) {
    $client = new Client();
    $oneWeekAgo = date('Y-m-d', strtotime('-1 week'));
    $firstOfMonth = date('Y-m-d', strtotime('first day of this month'));

    $commitsWeekResponse = $client->get("https://api.github.com/search/commits?q=author:$username+committer-date:>$oneWeekAgo", [
        'headers' => ['Accept' => 'application/vnd.github.v3+json'],
    ]);
    $commitsWeek = json_decode($commitsWeekResponse->getBody(), true);

    $commitsMonthResponse = $client->get("https://api.github.com/search/commits?q=author:$username+committer-date:>$firstOfMonth", [
        'headers' => ['Accept' => 'application/vnd.github.v3+json'],
    ]);
    $commitsMonth = json_decode($commitsMonthResponse->getBody(), true);

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
        $commitsWeek['total_count'],
        $commitsMonth['total_count'],
        $recentProjects,
        $username,
        date('Y-m-d H:i:s')
    ], $readmeTemplate);

    file_put_contents('README.md', $updatedReadme);
}

fetchGitHubData($username);
