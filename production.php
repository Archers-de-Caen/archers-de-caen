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
    return true;
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
    die("INFO - Aucune mise à jours");
}

echo "INFO - Téléchargement du repo depuis Github" . PHP_EOL;
file_put_contents(PROJECT_DOWNLOAD_NAME, file_get_contents(PROJECT_DOWNLOAD_URL));

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
if (!rename(PROJECT_DOWNLOAD_DIR . '/archers-de-caen-production', PRODUCING_PATH)) {
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

echo "INFO - composer install --no-dev" . PHP_EOL;
if (!shell_exec('php ' . COMPOSER_PATH . ' install --no-dev')) {
    die('ERROR - composer non exécuté');
}

echo "INFO - npm run build" . PHP_EOL;
if (!shell_exec(NPM_PATH . ' run build')) {
    die('ERROR - npm non exécuté');
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

echo "INFO - php bin/console d:m:m" . PHP_EOL;
if (!shell_exec('php ' . COMPOSER_PATH . ' d:m:m --no-interaction')) {
    die('ERROR - Migration non faite');
}

echo "INFO - Suppression du dossier de l'ancienne version" . PHP_EOL;
if (!rmdir(PRODUCTION_BK_PATH)) {
    die('ERROR - Ancienne version non supprimé');
}

echo "SUCCESS - Mise à jours terminé !" . PHP_EOL;
