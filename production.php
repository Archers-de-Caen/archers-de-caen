<?php

declare(strict_types=1);

const BASE_PATH = '/home/archerschl/';

const GET_COMMAND_ERROR = ' 2>&1';

const PHP_PATH = '/usr/local/php8.1/bin/php ';

const PROJECT_DOWNLOAD_NAME = BASE_PATH . '/production.zip';
const PROJECT_DOWNLOAD_DIR = BASE_PATH . '/download';
const CURRENT_VERSION_FILE = BASE_PATH . '/current-version.txt';

const PRODUCING_PATH = BASE_PATH . '/producing';
const PRODUCTION_PATH = BASE_PATH . '/v3';
const PRODUCTION_BK_PATH = PRODUCTION_PATH . '_bh';

const GITHUB_TOKEN = '!CHANGEME!';
const GITHUB_RELEASES_HOST = 'https://api.github.com/repos/Archers-de-Caen/archers-de-caen/releases';

function homePageWork(): bool
{
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, 'https://www.archers-caen.fr');

    curl_exec($curl);

    if (curl_errno($curl)) {
        return false;
    }

    $info = curl_getinfo($curl);

    curl_close($curl);

    if ($info['http_code']) {
        return true;
    }

    return false;
}

function downloadLastVersion(): string|false
{
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, GITHUB_RELEASES_HOST);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        "Authorization: Bearer " . GITHUB_TOKEN,
        "User-Agent: PHP",
    ]);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);

    $result = curl_exec($curl);

    if (!$result) {
        echo "ERROR - curl n'a pas marché";

        return false;
    }

    curl_close($curl);

    try {
        /** @var array $releases */
        $releases = json_decode($result, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        var_dump($e);

        return false;
    }

    usort($releases, static function (array $release1, array $release2): int {
        return DateTime::createFromFormat('c', $release1['published_at']) < DateTime::createFromFormat('c', $release2['published_at']) ? -1 : 1;
    });

    if (!count($releases)) {
        return false;
    }

    $lastRelease = $releases[array_key_last($releases)];

    if (file_get_contents(CURRENT_VERSION_FILE) === $lastRelease['tag_name']) {
        return false;
    }

    foreach ($lastRelease['assets'] as $asset) {
        if ('production.zip' === $asset['name']) {
            echo "INFO - Téléchargement de la version " . $lastRelease['tag_name'] . " depuis Github" . PHP_EOL;

            if (!$file = file_get_contents($asset['browser_download_url'])) {
                echo "ERROR - Téléchargement du repo depuis " . $asset['browser_download_url'] . PHP_EOL;

                return false;
            }

            if (!file_put_contents(PROJECT_DOWNLOAD_NAME, $file)) {
                echo "ERROR - Impossible d'écrire le fichier" . PHP_EOL;

                return false;
            }

            return $lastRelease['tag_name'];
        }
    }

    return false;
}

if (!$lastRelease = downloadLastVersion()) {
    die("INFO - Aucune mise à jours");
}

echo "INFO - Mise à jours vers la version ".$lastRelease;

echo "INFO - php --version" . PHP_EOL;
if (!$result = shell_exec(PHP_PATH . '--version' . GET_COMMAND_ERROR)) {
    die('ERROR - php non exécuté');
}
echo $result . PHP_EOL;

// Extraction du repo
$zip = new ZipArchive;
if ($zip->open(PROJECT_DOWNLOAD_NAME) === true) {
    $zip->extractTo(PROJECT_DOWNLOAD_DIR);
    $zip->close();
} else {
    die("ERROR - Repo non extrait");
}

echo "INFO - Suppression de l'archive du repo" . PHP_EOL;
if (!unlink(PROJECT_DOWNLOAD_NAME)) {
    die('ERROR - ZIP non supprimé');
}

echo "INFO - Restructuration des dossiers" . PHP_EOL;
if (!rename(PROJECT_DOWNLOAD_DIR . '/'. $lastRelease, PRODUCING_PATH)) {
    die('ERROR - Dossier non restructuré');
}

if (!rmdir(PROJECT_DOWNLOAD_DIR)) {
    die('ERROR - Dossier non supprimé');
}

echo "INFO - Copie du fichier .env vers .env.local" . PHP_EOL;
if (!copy(BASE_PATH . '/.env', PRODUCING_PATH . '/.env.local')) {
    die('ERROR - Fichier non copié');
}

echo "INFO - Positionnement de l'execution de PHP dans le repo" . PHP_EOL;
if (!chdir(PRODUCING_PATH)) {
    die('ERROR - Curseur php non déplacé');
}

echo "INFO - Déplacement de l'ancienne version vers un dossier temporaire" . PHP_EOL;
if (!rename(PRODUCTION_PATH, PRODUCTION_BK_PATH)) {
    die('ERROR - Ancienne version non déplacé');
}

echo "INFO - Déplacement de la nouvelle version vers le dossier de production" . PHP_EOL;
if (!rename(PRODUCING_PATH, PRODUCTION_PATH)) {
    die('ERROR - Nouvelle version non déplacé');
}

echo "INFO - Positionnement de l'execution de PHP dans le dossier de production";
if (!chdir(PRODUCTION_PATH)) {
    die('ERROR - Curseur php non déplacé');
}

echo "INFO - Suppression du dossier de l'ancienne version" . PHP_EOL;
if (!rmdir(PRODUCTION_BK_PATH)) {
    die('ERROR - Ancienne version non supprimé');
}

if (!file_put_contents(PROJECT_DOWNLOAD_NAME, $lastRelease)) {
    echo "ERROR - Impossible d'écrire le fichier" . PHP_EOL;

    return false;
}

echo "SUCCESS - Mise à jours terminé !" . PHP_EOL;
