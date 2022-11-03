<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use App\Domain\Archer\Model\Archer;
use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Config\Status;
use App\Domain\Cms\Repository\DataRepository;
use App\Domain\Cms\Repository\PageRepository;
use App\Helper\PaginatorHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    public const ROUTE_LANDING_INDEX = 'landing_index';

    #[Route('/', name: self::ROUTE_LANDING_INDEX)]
    public function index(PageRepository $pageRepository, DataRepository $dataRepository): Response
    {
        $actualityLocked = null;
        if ($actualityLockedData = $dataRepository->findOneBy(['code' => 'INDEX_ACTUALITY_LOCKED'])?->getContent()) {
            $actualityLocked = $pageRepository->findOneBy(['slug' => $actualityLockedData[array_key_first($actualityLockedData)]]);
        }

        $actualities = $pageRepository->findBy(
            [
                'category' => Category::ACTUALITY->value,
                'status' => Status::PUBLISH,
            ],
            ['createdAt' => 'DESC'],
            $actualityLocked ? 3 : 4
        );

        if ($actualityLocked) {
            $actualities[] = $actualityLocked;
        }

        return $this->render('/landing/index/index.html.twig', [
            'actualities' => $actualities,
            'contents' => $dataRepository->findOneBy(['code' => 'INDEX_PAGE_ELEMENT'])?->getContent(),
            'partners' => $dataRepository->findOneBy(['code' => 'PARTNER'])?->getContent(),
        ]);
    }

    public const ROUTE_LANDING_STYLE_GUIDE = 'landing_style_guide';

    #[Route('/style-guide', name: self::ROUTE_LANDING_STYLE_GUIDE)]
    public function styleGuide(Request $request): Response
    {
        if (!$this->isGranted(Archer::ROLE_DEVELOPER) && 'dev' !== $request->server->get('APP_ENV')) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('/landing/style-guide/style-guide.html.twig', [
            'paginator' => PaginatorHelper::pagination((int) ($request->query->get('page') ?: 1), 100),
        ]);
    }
}
