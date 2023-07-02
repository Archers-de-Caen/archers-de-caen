<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\Developer\Liip;

use App\Helper\PaginatorHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    '/liip/image-list',
    name: self::ROUTE,
    methods: Request::METHOD_GET,
)]
class ListPathsLiipAdminController extends AbstractController
{
    public const ROUTE = 'admin_developer_admin_liip_image_cache';

    /**
     * @throws \Exception
     */
    public function __invoke(Request $request, ParameterBagInterface $parameterBag): Response
    {
        /** @var array $resolvers */
        $resolvers = $parameterBag->get('liip_imagine.resolvers');
        $webPath = $resolvers['cache_provider']['web_path'];
        $cacheRoot = $webPath['web_root'].'/'.$webPath['cache_prefix'];

        /** @var array $loaders */
        $loaders = $parameterBag->get('liip_imagine.loaders');
        $dataRoot = $loaders['default']['filesystem']['data_root'][0];

        /** @var int $page */
        $page = $request->query->get('page', '1');
        $elements = 250;

        $finder = new Finder();

        $files = $finder->files()->in($dataRoot)->getIterator();
        $files = iterator_to_array($files);

        $paths = [];

        foreach ($files as $file) {
            $paths[$file->getRealPath()] = [];

            $cachedFiles = (new Finder())->files()->in($cacheRoot)->name('*'.$file->getFilename().'*');

            foreach ($cachedFiles as $cachedFile) {
                $paths[$file->getRealPath()][] = $cachedFile->getRealPath();
            }
        }

        uasort($paths, static fn ($a, $b) => \count($a) <=> \count($b));
        $paths = \array_slice($paths, ($page - 1) * $elements, $elements, true);

        return $this->render('admin/developer/image-cache.html.twig', [
            'paths' => $paths,
            'paginator' => PaginatorHelper::pagination($page + 1, (int) ceil(\count($files) / $elements)),
            'currentPage' => $page,
        ]);
    }
}
