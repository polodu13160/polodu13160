<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;

$username = 'polodu13160';
$readmeTemplate = file_get_contents('README.md');

function fetchGitHubData($username) {
    $client = new Client();
    $reposResponse = $client->get("https://api.github.com/users/$username/repos?sort=created&per_page=3");
    $repos = json_decode($reposResponse->getBody(), true);

    $recentProjects = '';
    foreach ($repos as $repo) {
        $recentProjects .= "| 🟢 **{$repo['name']}** | [Accéder au repo]({$repo['html_url']}) |\n";
    }

    global $readmeTemplate;
    $updatedContent = "
<!--START_SECTION:README-->
### 📂 Projets récents

| Nom du Projet       | Lien GitHub                                        |
|---------------------|----------------------------------------------------|
$recentProjects

---

<div style=\"color: #00ff00; font-family: 'Courier New', monospace; background-color: black; padding: 10px; border-radius: 5px;\">
  <p>System Status: <strong>Online</strong></p>
  <p>Last Update: <strong>" . date('Y-m-d H:i:s') . "</strong></p>
</div>
<!--END_SECTION:README-->
";

    $readmeTemplate = preg_replace('/<!--START_SECTION:README-->.*<!--END_SECTION:README-->/s', $updatedContent, $readmeTemplate);
    file_put_contents('README.md', $readmeTemplate);
}

fetchGitHubData($username);
