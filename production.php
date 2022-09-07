<?php

declare(strict_types=1);

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

function websiteNeedUpgrade(): bool
{
    return false;
}

const BASE_PATH = '/home/archerschl/';

const NODE_PATH = BASE_PATH . 'bin/node-v16.17.0-linux-x64/bin/node ';
const NPM_PATH = NODE_PATH . BASE_PATH . 'bin/node-v16.17.0-linux-x64/lib/node_modules/npm/bin/npm-cli.js ';
const COMPOSER_PATH = BASE_PATH . 'bin/composer.phar';

const PROJECT_DOWNLOAD_URL = 'https://github.com/Archers-de-Caen/archers-de-caen/archive/refs/heads/production.zip';
const PROJECT_DOWNLOAD_NAME = BASE_PATH . '/production.zip';
const PROJECT_DOWNLOAD_DIR = BASE_PATH . '/download';

const PRODUCING_PATH = BASE_PATH . '/producing';
const PRODUCTION_PATH = BASE_PATH . '/www';
const PRODUCTION_BK_PATH = PRODUCTION_PATH . '_bh';

// On vérifie si le site est sur le dernier commit
if (!websiteNeedUpgrade()) {
    echo "Aucune mise à jours".PHP_EOL;

    die;
}

echo "Téléchargement du repo depuis Github" . PHP_EOL;
file_put_contents(PROJECT_DOWNLOAD_NAME, file_get_contents(PROJECT_DOWNLOAD_URL));

// Extraction du repo
$zip = new ZipArchive;
if ($zip->open(PROJECT_DOWNLOAD_NAME) === true) {
    $zip->extractTo(PROJECT_DOWNLOAD_DIR);
    $zip->close();

    echo "Repo extrait" . PHP_EOL;
} else {
    echo "Erreur dans l'extraction du repo" . PHP_EOL;

    die;
}

echo "Suppression de l'archive du repo" . PHP_EOL;
unlink(PROJECT_DOWNLOAD_NAME);

echo "Restructuration des dossiers" . PHP_EOL;
rename(PROJECT_DOWNLOAD_DIR . '/archers-de-caen-production', PRODUCING_PATH);
rmdir(PROJECT_DOWNLOAD_DIR);

echo "Copie du fichier .env vers .env.local" . PHP_EOL;
copy(BASE_PATH . '/.env', PRODUCING_PATH . '/.env.local');

echo "Positionnement de l'execution de PHP dans le repo";
chdir(PRODUCING_PATH);

echo "composer install --no-dev" . PHP_EOL;
shell_exec('php ' . COMPOSER_PATH . ' install --no-dev');

echo "npm run build" . PHP_EOL;
shell_exec(NPM_PATH . ' run build');

echo "Déplacement de l'ancienne version vers un dossier temporaire" . PHP_EOL;
rename(PRODUCTION_PATH, PRODUCTION_BK_PATH);

echo "Déplacement de la nouvelle version vers le dossier de production" . PHP_EOL;
rename(PRODUCING_PATH, PRODUCTION_PATH);

echo "Positionnement de l'execution de PHP dans le dossier de production";
chdir(PRODUCTION_PATH);

echo "php bin/console d:m:m" . PHP_EOL;
shell_exec('php ' . COMPOSER_PATH . ' d:m:m --no-interaction');

echo "Suppression du dossier de l'ancienne version" . PHP_EOL;
rmdir(PRODUCTION_BK_PATH);

echo "Mise à jours terminé !" . PHP_EOL;
