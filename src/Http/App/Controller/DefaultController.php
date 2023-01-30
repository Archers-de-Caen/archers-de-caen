<?php

declare(strict_types=1);

namespace App\Http\App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

#[IsGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED)]
final class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('/app/index/index.html.twig');
    }
}
