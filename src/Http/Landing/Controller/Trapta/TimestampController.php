<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Trapta;

use App\Domain\Competition\Repository\TraptaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    path: '/trapta/timestamps.json', // le .json est dû au fonctionnement de TraptaCloud
    name: self::ROUTE,
    methods: [Request::METHOD_POST]
)]
class TimestampController extends AbstractController
{
    public const ROUTE = 'landing_trapta_timestamp';

    public function __invoke(Request $request, TraptaRepository $traptaRepository): Response
    {
        $eventName = $request->get('eventname');

        $event = $traptaRepository->findOneBy(['eventName' => $eventName]);

        if (!$event) {
            throw $this->createNotFoundException();
        }

        return $this->json([
            'timestamp' => $event->getUpdatedAt()?->getTimestamp(),
        ]);
    }
}
