<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\CompetitionRegister\Payment;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(
    path: '/inscription-concours/{slug}/paiement/{licenseNumber}/erreur',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
class ErrorController extends AbstractController
{
    public const ROUTE = 'landing_competition_register_payment_error';

    public function __invoke(Request $request, string $slug, string $licenseNumber): Response
    {
        $this->addFlash(
            'danger',
            'Une erreur est survenue, votre paiement n\'a pas abouti: '.$request->query->get('error')
        );

        return $this->redirectToRoute(self::ROUTE, [
            'slug' => $slug,
            'licenseNumber' => $licenseNumber,
        ]);
    }
}
