<?php

declare(strict_types=1);

namespace App\Http\App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED)]
#[Route(
    path: '/',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
final class DefaultController extends AbstractController
{
    public const string ROUTE = 'app_index';

    public function __invoke(): Response
    {
        return $this->render('/app/index/index.html.twig');
    }
}
