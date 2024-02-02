<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\CompetitionRegister\Payment;

use App\Http\Landing\Controller\CompetitionRegister\RecapController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(
    path: '/inscription-concours/{slug}/paiement/{licenseNumber}/fini',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
class EndController extends AbstractController
{
    public const ROUTE = 'landing_competition_register_payment_end';

    public function __invoke(Request $request, string $slug, string $licenseNumber): Response
    {
        if ('succeeded' === $request->query->get('code')) {
            $this->addFlash('success', 'Nous avons bien reçu votre paiement');
        } else {
            $this->addFlash('danger', "Une erreur est survenue, votre paiement n'a pas abouti");
        }

        return $this->redirectToRoute(RecapController::ROUTE, [
            'slug' => $slug,
            'licenseNumber' => $licenseNumber,
        ]);
    }
}
