<?php

declare(strict_types=1);

const BASE_PATH = '/home/archerschl';

const GET_COMMAND_ERROR = ' 2>&1';

const PROJECT_ZIP = BASE_PATH . '/production.zip';
const CURRENT_VERSION_FILE = BASE_PATH . '/current-version.txt';

const PRODUCING_PATH = BASE_PATH . '/producing';
const PRODUCTION_PATH = BASE_PATH . '/v3';
const PRODUCTION_BK_PATH = PRODUCTION_PATH . '_bk';

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

            if (!file_put_contents(PROJECT_ZIP, $file)) {
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

// Extraction du repo
$zip = new ZipArchive;
if ($zip->open(PROJECT_ZIP) === true) {
    $zip->extractTo(PRODUCING_PATH);
    $zip->close();
} else {
    die("ERROR - Repo non extrait");
}

echo "INFO - Suppression de l'archive du repo" . PHP_EOL;
if (!unlink(PROJECT_ZIP)) {
    die('ERROR - ZIP non supprimé');
}

echo "INFO - Copie du fichier .env vers .env.local" . PHP_EOL;
if (!copy(BASE_PATH . '/.env', PRODUCING_PATH . '/.env.local')) {
    die('ERROR - Fichier non copié');
}

if (!file_put_contents(PRODUCING_PATH . '/.env.local', "\n\nRELEASE=$lastRelease", FILE_APPEND)) {
    die("ERROR - Impossible d'écrire le fichier" . PHP_EOL);
}

echo "INFO - Suppression du dossier de l'ancienne version" . PHP_EOL;
if (is_dir(PRODUCTION_BK_PATH) && !shell_exec('rm ' . PRODUCTION_BK_PATH . ' -rf' . GET_COMMAND_ERROR)) {
    die('ERROR - Ancienne version non supprimé');
}

echo "INFO - Déplacement de l'ancienne version vers un dossier temporaire" . PHP_EOL;
if (!rename(PRODUCTION_PATH, PRODUCTION_BK_PATH)) {
    die('ERROR - Ancienne version non déplacé');
}

echo "INFO - Déplacement de la nouvelle version vers le dossier de production" . PHP_EOL;
if (!rename(PRODUCING_PATH, PRODUCTION_PATH)) {
    die('ERROR - Nouvelle version non déplacé');
}

if (!file_put_contents(CURRENT_VERSION_FILE, $lastRelease)) {
    die("ERROR - Impossible d'écrire le fichier" . PHP_EOL);
}

echo "SUCCESS - Mise à jours terminé !" . PHP_EOL;
