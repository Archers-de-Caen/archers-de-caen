<?php

declare(strict_types=1);

namespace App\Domain\Competition\Config;

enum Level: string
{
    case DEPARTMENTAL = 'departmental';
    case REGIONAL = 'regional';
    case NATIONAL = 'national';
    case FRENCH_CHAMPIONSHIP = 'french_championship';
    case FRENCH_CUP_CHAMPIONSHIP = 'french_cup_championship';
    case FRENCH_CLUB_TEAM_CHAMPIONSHIP = 'french_club_team_championship';
    case INTERNATIONAL_CHAMPIONSHIP = 'international_championship';
    case EUROPEAN_CHAMPIONSHIP = 'european_championship';
    case WORLD_CHAMPIONSHIP = 'world_championship';
    case CLUB_CHAMPIONSHIP = 'club_championship';
    case ABLE_BODIED_PARA = 'able_bodied_para';

    public function fromFftaCode(string $fftaCode): self
    {
        return match ($fftaCode) {
            'D' => self::DEPARTMENTAL,
            'R' => self::REGIONAL,
            'A' => self::NATIONAL,
            'N' => self::FRENCH_CHAMPIONSHIP,
            'C' => self::FRENCH_CUP_CHAMPIONSHIP,
            'O' => self::FRENCH_CLUB_TEAM_CHAMPIONSHIP,
            'I' => self::INTERNATIONAL_CHAMPIONSHIP,
            'E' => self::EUROPEAN_CHAMPIONSHIP,
            'M' => self::WORLD_CHAMPIONSHIP,
            'Z' => self::CLUB_CHAMPIONSHIP,
            'H' => self::ABLE_BODIED_PARA,
        };
    }
}
