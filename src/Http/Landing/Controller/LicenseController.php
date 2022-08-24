<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LicenseController extends AbstractController
{
    public const ROUTE_LANDING_LICENSE_NEW = 'landing_license_new';

    #[Route('/prendre-une-licence', name: self::ROUTE_LANDING_LICENSE_NEW)]
    public function index(): Response
    {
        return $this->render('/landing/license/new.html.twig');
    }
}
