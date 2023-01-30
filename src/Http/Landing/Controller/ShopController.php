<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ShopController extends AbstractController
{
    public const ROUTE_LANDING_INDEX = 'landing_shop';

    #[Route('/boutique', name: self::ROUTE_LANDING_INDEX)]
    public function shop(): Response
    {
        return $this->render('/landing/shop/index.html.twig', [
        ]);
    }
}
