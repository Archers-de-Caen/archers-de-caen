<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Trapta\Update;

use App\Domain\Competition\Model\Trapta;
use App\Domain\Competition\Repository\TraptaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    path: '/trapta/updatematches.php', // le .php est dû au fonctionnement de TraptaCloud
    name: self::ROUTE,
    methods: [Request::METHOD_POST]
)]
class UpdateMatchesController extends AbstractController
{
    public const ROUTE = 'landing_trapta_update_matches';

    public function __invoke(
        Request $request,
        ParameterBagInterface $parameterBag,
        TraptaRepository $traptaRepository
    ): Response {
        if ($parameterBag->get('trapta_password') !== $request->get('password')) {
            throw $this->createAccessDeniedException();
        }

        /** @var string $data */
        $data = $request->get('data');

        /** @var string $eventName */
        $eventName = $request->get('eventname');

        $event = $traptaRepository->findOneBy(['eventName' => $eventName]) ?? new Trapta();

        try {
            /** @var array|null $dataDecoded */
            $dataDecoded = json_decode($data, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->json('Data incorrect', Response::HTTP_BAD_REQUEST);
        }

        if (!$dataDecoded) {
            return $this->json('Data incorrect', Response::HTTP_BAD_REQUEST);
        }

        $matches = $event->getMatches();

        $matches[$dataDecoded['title']] = $dataDecoded;

        $event
            ->setEventName($eventName)
            ->setMatches($matches)
        ;

        $traptaRepository->save($event, true);

        return $this->json('OK');
    }
}
