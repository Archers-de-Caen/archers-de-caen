<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Page;

use App\Domain\Cms\Model\Data;
use App\Domain\Cms\Repository\DataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(
    path: '/calendrier',
    name: self::ROUTE,
    options: ['sitemap' => true],
    methods: Request::METHOD_GET
)]
final class CalendarController extends AbstractController
{
    public const string ROUTE = 'landing_club_calendar';

    public function __construct(
        private readonly DataRepository $dataRepository
    ) {
    }

    public function __invoke(): Response
    {
        $calendarIframeSrc = $this->dataRepository->getText(Data::CODE_CALENDAR_IFRAME_SRC);

        return $this->render('/landing/club/calendar.html.twig', [
            'calendarIframeSrc' => $calendarIframeSrc,
        ]);
    }
}
