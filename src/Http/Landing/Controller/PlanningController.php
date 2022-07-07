<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use App\Domain\Archer\Model\Archer;
use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Repository\PageRepository;
use App\Helper\PaginatorHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlanningController extends AbstractController
{
    public const ROUTE_LANDING_PLANNING = 'landing_planning';

    #[Route('/planning', name: self::ROUTE_LANDING_PLANNING)]
    public function index(): Response
    {
        return $this->render('/landing/planning/planning.html.twig');
    }
}
