<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\FFTA;

use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Gender;
use App\Domain\Archer\Config\Weapon;
use App\Domain\Competition\Config\Type;
use App\Infrastructure\Service\ArcheryService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class FFTAExtranetService
{
    private const int STRUCTURE_ID = 657;

    private array $cookies = [];

    /**
     * @param string $fftaUsername Injected from service.yaml
     * @param string $fftaPassword Injected from service.yaml
     */
    public function __construct(
        private HttpClientInterface $httpClient,
        private readonly string $fftaUsername,
        private readonly string $fftaPassword,
        private readonly LoggerInterface $logger,
    ) {
        $this->httpClient = $this->httpClient->withOptions([
            'base_uri' => 'https://extranet.ffta.fr/',
        ]);
    }

    /**
     * @throws HttpExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function connect(): void
    {
        if (!$this->fftaUsername || !$this->fftaPassword) {
            throw new \RuntimeException('FFTA credentials are missing');
        }

        $response = $this->httpClient->request(
            method: Request::METHOD_POST,
            url: '/',
            options: [
                'headers' => [
                    'Content-Type' => 'multipart/form-data',
                ],
                'body' => [
                    'login' => [
                        'identifiant' => $this->fftaUsername,
                        'idpassword' => $this->fftaPassword,
                    ],
                ],
            ]
        );

        $this->setCookiesFromResponse($response);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws HttpExceptionInterface
     */
    private function setCookiesFromResponse(ResponseInterface $response): void
    {
        $this->cookies = $response->getHeaders()['set-cookie'];
    }

    /**
     * @return array<string, array<CompetitionResultDTO>>
     *
     * @throws HttpExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    public function getCompetitionResults(?CompetitionResultSearchDTO $search = null): array
    {
        if (!$search instanceof CompetitionResultSearchDTO) {
            $search = new CompetitionResultSearchDTO(
                season: ArcheryService::getCurrentSeason(),
            );
        }

        $pageConnexion = new \DOMDocument();
        $pageConnexion->validateOnParse = true;

        $response = $this->httpClient->request(
            method: Request::METHOD_POST,
            url: 'extractions/eprv-resind.html',
            options: [
                'headers' => [
                    'Cookie' => $this->cookies,
                ],
                'body' => [
                    'search' => [
                        'Saison' => $search->getSeason(),
                        'Discipline' => 'all',
                        'TypeChampionnat' => 'all',
                        'Pers' => 'CLU',
                        'oldPers' => 'CLU',
                        'Struc' => self::STRUCTURE_ID,
                        'Date_dbt' => $search->getDateStart() ? $search->getDateStart()->format('d/m/Y') : '01/01/'.($search->getSeason() - 1),
                        'Date_fin' => $search->getDateEnd() ? $search->getDateEnd()->format('d/m/Y') : '31/12/'.($search->getSeason() + 1),
                    ],
                    'StartGen' => 'Générer les documents',
                ],
            ],
        );

        $pageConnexion->loadHTML($response->getContent(), \LIBXML_NOERROR);

        $nodes = $pageConnexion->getElementById('exports_container')?->getElementsByTagName('a');

        if (!$nodes) {
            throw new \RuntimeException('CSV download link not found');
        }

        $csvDownloadLink = null;

        foreach ($nodes as $node) {
            $href = $node->getAttribute('href');

            if (str_starts_with($href, 'https://extranet.ffta.fr/tmp/resultats/ResultatsIndividuels')) {
                $csvDownloadLink = $href;

                break;
            }
        }

        if (!$csvDownloadLink) {
            throw new \RuntimeException('CSV download link not found');
        }

        $response = $this->httpClient->request(
            method: Request::METHOD_GET,
            url: str_replace('https://extranet.ffta.fr/', '', $csvDownloadLink),
            options: [
                'headers' => [
                    'Cookie' => $this->cookies,
                ],
            ],
        );

        $competitionResult = $this->createCompetitionResultDTOListFromCsv($response->getContent());

        return $this->groupResultsByCompetition($competitionResult);
    }

    /**
     * @return array<CompetitionResultDTO>
     *
     * @throws \Exception
     */
    public function createCompetitionResultDTOListFromCsv(string $csv): array
    {
        /*
         * 0: "SAISON", 1: "DISCIPLINE", 2: "NO_LICENCE", 3: "NOM_PERSONNE", 4: "PRENOM_PERSONNE", 5: "HORS_F",
         * 6: "SEXE_PERSONNE", 7: "CAT", 8: "CAT_S", 9: "CODE_STRUCTURE", 10: "NOM_STRUCTURE", 11: "ARME", 12: "NIVEAU",
         * 13: "SCORE", 14: "PAILLE", 15: "DIX", 16: "NEUF", 17: "DISTANCE", 18: "BLASON", 19: "D_DEBUT_CONCOURS",
         * 20: "D_FIN_CONCOURS", 21: "LIEU_CONCOURS", 22: "CODE_STRUCTURE_ORGANISATRICE", 23: "NOM_STRUCTURE_ORGANISATRICE",
         * 24: "FORMULE_TIR", 25: "NIVEAU_CHPT", 26: "DETAIL_NIVEAU_CHPT", 27: "DISTINCTION", 28: "PLACE_QUALIF",
         * 29: "SCORE_DIST1", 30: "SCORE_DIST2", 31: "SCORE_DIST3", 32: "SCORE_DIST4", 33: "SCORE_32", 34: "SCORE_16",
         * 35: "SCORE_8", 36: "SCORE_QUART", 37: "SCORE_DEMI", 38: "SCORE_PETITE_FINAL", 39: "SCORE_FINAL", 40: "PLACE_DEF",
         * 41: "NUM_DEPART", 42: "EPRV_NOM", 43: "CAT_TIR", 44: "CAT_CLASS"
         */

        $lines = explode("\n", $csv);
        $results = [];

        foreach (\array_slice($lines, 1) as $line) {
            $data = str_getcsv($line, ';');

            if (\count($data) < 40) {
                continue;
            }

            try {
                $results[] = $this->createCompetitionResultDTOFromCsvRow($data);
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage());

                continue;
            }
        }

        return $results;
    }

    /**
     * @throws \Exception
     */
    public function createCompetitionResultDTOFromCsvRow(array $competitionResult): CompetitionResultDTO
    {
        if (\count($competitionResult) < 40) {
            throw new \Exception('Invalid competition result');
        }

        $gender = Gender::createFromString($competitionResult[6]);
        $genderLongString = match ($gender) {
            Gender::MAN => 'Homme',
            Gender::WOMAN => 'Femme',
            Gender::OTHER, Gender::UNDEFINED => throw new \ValueError('Gender not found '.$competitionResult[6]),
        };
        $weapon = Weapon::createFromString($competitionResult[11]);
        $distance = (int) $competitionResult[17];
        $target = (int) $competitionResult[18];
        $archerCategory = Category::createFromString($competitionResult[7].' '.$genderLongString);
        $competitionTypeFFTACode = $competitionResult[1];
        $competitionType = Type::createFromFFTACode(
            fftaCode: $competitionTypeFFTACode,
            distance: $distance,
            archerCategory: $archerCategory,
            weapon: $weapon,
            target: $target,
            shootFormule: $competitionResult[24],
        );
        $categoryOverRanking = $competitionResult[8] ? Category::createFromString($competitionResult[8].' '.$genderLongString) : null;

        $dateStart = \DateTimeImmutable::createFromFormat('Y-m-d', $competitionResult[19]);

        if (false === $dateStart) {
            throw new \Exception(\sprintf('Invalid date start format "%s"', $competitionResult[19]));
        }

        $dateEnd = \DateTimeImmutable::createFromFormat('Y-m-d', $competitionResult[20]);

        if (false === $dateEnd) {
            throw new \Exception(\sprintf('Invalid date end format "%s"', $competitionResult[20]));
        }

        return new CompetitionResultDTO(
            season: (int) $competitionResult[0],
            discipline: $competitionType,
            licenseNumber: $competitionResult[2],
            lastName: $competitionResult[3],
            firstName: $competitionResult[4],
            outOfFrance: $competitionResult[5],
            gender: $gender,
            category: $archerCategory,
            categoryOverRanking: $categoryOverRanking,
            structureCode: $competitionResult[9],
            structureName: $competitionResult[10],
            weapon: $weapon,
            level: $competitionResult[12],
            score: (int) $competitionResult[13],
            straw: (int) $competitionResult[14],
            ten: (int) $competitionResult[15],
            nine: (int) $competitionResult[16],
            distance: $distance,
            target: $target,
            startCompetitionDate: $dateStart,
            endCompetitionDate: $dateEnd,
            location: $competitionResult[21],
            organizerStructureCode: $competitionResult[22],
            organizerStructureName: $competitionResult[23],
            shootingFormula: $competitionResult[24],
            championshipLevel: $competitionResult[25],
            championshipLevelDetail: $competitionResult[26],
            distinction: $competitionResult[27],
            qualificationPlace: (int) $competitionResult[28],
            scoreDist1: (int) $competitionResult[29],
            scoreDist2: (int) $competitionResult[30],
            scoreDist3: (int) $competitionResult[31],
            scoreDist4: (int) $competitionResult[32],
            score32: $competitionResult[33] ? (int) $competitionResult[33] : null,
            score16: $competitionResult[34] ? (int) $competitionResult[34] : null,
            score8: $competitionResult[35] ? (int) $competitionResult[35] : null,
            scoreQuarter: $competitionResult[36] ? (int) $competitionResult[36] : null,
            scoreSemi: $competitionResult[37] ? (int) $competitionResult[37] : null,
            scoreSmallFinal: $competitionResult[38] ? (int) $competitionResult[38] : null,
            scoreFinal: $competitionResult[39] ? (int) $competitionResult[39] : null,
            finalPlace: (int) $competitionResult[40],
            startNumber: (int) $competitionResult[41],
            eprvName: $competitionResult[42],
            shootingCategory: $competitionResult[43] ?? null,
            shootingCategoryClass: $competitionResult[44] ?? null,
        );
    }

    /**
     * @param array<int, CompetitionResultDTO> $competitionResult
     *
     * @return array<string, array<CompetitionResultDTO>>
     */
    private function groupResultsByCompetition(array $competitionResult): array
    {
        $result = [];

        foreach ($competitionResult as $competitionResultDTO) {
            $result[$competitionResultDTO->getEventCode()][] = $competitionResultDTO;
        }

        return $result;
    }
}
