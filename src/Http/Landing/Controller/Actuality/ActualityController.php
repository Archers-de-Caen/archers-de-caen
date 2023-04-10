<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Actuality;

use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Config\Status;
use App\Domain\Cms\Model\Page;
use App\Domain\Cms\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    path: '/actualite/{slug}',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
class ActualityController extends AbstractController
{
    public const ROUTE = 'landing_actuality';

    public function __invoke(Page $actuality, PageRepository $pageRepository): Response
    {
        $pages = $pageRepository
            ->findBy([
                'category' => Category::ACTUALITY->value,
                'status' => Status::PUBLISH->value,
            ], ['createdAt' => 'DESC']);

        foreach ($pages as $key => $page) {
            if ($page->getId() === $actuality->getId()) {
                $nextPage = $pages[$key + 1] ?? null;

                break;
            }
        }

        return $this->render('/landing/actualities/actuality.html.twig', [
            'page' => $actuality,
            'nextPage' => $nextPage ?? null,
        ]);
    }
}
