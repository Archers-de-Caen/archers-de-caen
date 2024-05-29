<?php

declare(strict_types=1);

/**
 * declare(strict_types=1);
 *
 * require_once __DIR__.'/Deploy.php';
 *
 * (new Deploy('!CHANGE_ME!', '!CHANGE_ME!'))(apache_request_headers()['Authorization'] ?? null);
 */
class Deploy
{
    private const string BASE_PATH = '/home/archerschl';
    private const string PRODUCTION_PATH = self::BASE_PATH.'/v3';

    private const string PHP_EXECUTABLE = '/usr/local/php8.3/bin/php ';

    private const string GET_COMMAND_ERROR = ' 2>&1';

    private const string PROJECT_ZIP = self::BASE_PATH.'/production.zip';
    private const string CURRENT_VERSION_FILE = self::BASE_PATH.'/current-version.txt';

    private const string PRODUCING_PATH = self::BASE_PATH.'/producing';
    private const string PRODUCTION_BK_PATH = self::PRODUCTION_PATH.'_bk';

    private const string GITHUB_RELEASES_HOST = 'https://api.github.com/repos/Archers-de-Caen/archers-de-caen/releases';

    public const string EMAIL = 'site@archers-caen.fr';

    private array $logs = [];

    public function __construct(
        private readonly string $githubToken,
        private readonly string $deployPassword,
    ) {
    }

    public function __invoke(?string $authorization): void
    {
        if ($this->deployPassword !== $authorization) {
            $this->sendEmailDeployment('ERROR - Mot de passe incorrect');

            return;
        }

        if (!$lastRelease = $this->downloadLastVersion()) {
            $this->sendEmailDeployment('INFO - Aucune mise à jours');

            return;
        }

        // Extraction du repo
        $zip = new ZipArchive();
        if (true === $zip->open(self::PROJECT_ZIP)) {
            $zip->extractTo(self::PRODUCING_PATH);
            $zip->close();
        } else {
            $this->sendEmailDeployment('ERROR - Repo non extrait');

            return;
        }

        $this->logs("INFO - Suppression de l'archive du repo");
        if (!unlink(self::PROJECT_ZIP)) {
            $this->sendEmailDeployment('ERROR - ZIP non supprimé');

            return;
        }

        $this->logs('INFO - Copie du fichier .env vers .env.local');
        if (!copy(self::BASE_PATH.'/.env', self::PRODUCING_PATH.'/.env.local')) {
            $this->sendEmailDeployment('ERROR - Fichier non copié');

            return;
        }

        if (!file_put_contents(self::PRODUCING_PATH.'/.env.local', "\n\nRELEASE=$lastRelease", \FILE_APPEND)) {
            $this->sendEmailDeployment("ERROR - Impossible d'écrire le fichier .env.local");

            return;
        }

        $this->logs("INFO - Suppression du dossier de l'ancienne version");
        if (is_dir(self::PRODUCTION_BK_PATH)) {
            shell_exec('rm '.self::PRODUCTION_BK_PATH.' -rf'.self::GET_COMMAND_ERROR);

            clearstatcache(true, self::PRODUCTION_BK_PATH);

            if (is_dir(self::PRODUCTION_BK_PATH)) {
                $this->sendEmailDeployment('ERROR - Ancienne version non supprimé');

                return;
            }
        }

        $this->logs("INFO - Déplacement de l'ancienne version vers un dossier temporaire");
        if (!rename(self::PRODUCTION_PATH, self::PRODUCTION_BK_PATH)) {
            $this->sendEmailDeployment('ERROR - Ancienne version non déplacé');

            return;
        }

        $this->logs('INFO - Déplacement de la nouvelle version vers le dossier de production');
        if (!rename(self::PRODUCING_PATH, self::PRODUCTION_PATH)) {
            $this->sendEmailDeployment('ERROR - Nouvelle version non déplacé');

            return;
        }

        $this->logs('INFO - Migration base de donnée');
        if (!shell_exec(self::PHP_EXECUTABLE.self::PRODUCTION_PATH.'/bin/console doctrine:migration:migrate --no-interaction'.self::GET_COMMAND_ERROR)) {
            $this->sendEmailDeployment('ERROR - Migration base de donnée impossible');

            return;
        }

        if (!file_put_contents(self::CURRENT_VERSION_FILE, $lastRelease)) {
            $this->sendEmailDeployment("ERROR - Impossible d'écrire le fichier current-version.txt");

            return;
        }

        $this->sendEmailDeployment('SUCCESS - Mise à jours terminé !');
    }

    private function downloadLastVersion(): string|false
    {
        $curl = curl_init();

        curl_setopt($curl, \CURLOPT_URL, self::GITHUB_RELEASES_HOST);
        curl_setopt($curl, \CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer '.$this->githubToken,
            'User-Agent: PHP',
        ]);
        curl_setopt($curl, \CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, \CURLOPT_FOLLOWLOCATION, 1);

        $result = curl_exec($curl);

        if (!$result) {
            echo "ERROR - curl n'a pas marché";

            return false;
        }

        curl_close($curl);

        try {
            /** @var array $releases */
            $releases = json_decode($result, true, 512, \JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            echo 'ERROR - '.$e->getMessage().\PHP_EOL;

            return false;
        }

        usort($releases, static function (array $release1, array $release2): int {
            return DateTime::createFromFormat('c', $release1['published_at']) < DateTime::createFromFormat('c', $release2['published_at']) ? -1 : 1;
        });

        if (!count($releases)) {
            return false;
        }

        $lastRelease = $releases[array_key_last($releases)];

        if (file_get_contents(self::CURRENT_VERSION_FILE) === $lastRelease['tag_name']) {
            return false;
        }

        foreach ($lastRelease['assets'] as $asset) {
            if ('production.zip' === $asset['name']) {
                $this->logs('INFO - Téléchargement de la version '.$lastRelease['tag_name'].' depuis Github'.\PHP_EOL);

                if (!$file = file_get_contents($asset['browser_download_url'])) {
                    $this->logs('ERROR - Téléchargement du repo depuis '.$asset['browser_download_url']);

                    return false;
                }

                if (!file_put_contents(self::PROJECT_ZIP, $file)) {
                    $this->logs("ERROR - Impossible d'écrire le fichier");

                    return false;
                }

                return $lastRelease['tag_name'];
            }
        }

        return false;
    }

    private function sendEmailDeployment(string $status): void
    {
        $message = 'Voici le résultat du déploiement du site archers-de-caen.fr :'.\PHP_EOL.\PHP_EOL;
        $message .= implode(\PHP_EOL, $this->logs);

        mail(self::EMAIL, '[ARCHERS DE CAEN] Déploiement: '.$status, $message);
    }

    private function logs(string $message): void
    {
        $this->logs[] = $message;

        echo $message.\PHP_EOL;
    }
}
