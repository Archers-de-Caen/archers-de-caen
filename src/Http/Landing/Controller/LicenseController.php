<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use App\Domain\Cms\Repository\DataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(
    path: '/prendre-une-licence',
    name: self::ROUTE,
    options: ['sitemap' => true],
    methods: Request::METHOD_GET
)]
class LicenseController extends AbstractController
{
    public const ROUTE = 'landing_license_new';

    public function __invoke(DataRepository $dataRepository): Response
    {
        return $this->render('/landing/license/new.html.twig', [
            'takeLicense' => $dataRepository->findOneBy(['code' => 'TAKE_LICENSE'])?->getContent(),
        ]);
    }
}
