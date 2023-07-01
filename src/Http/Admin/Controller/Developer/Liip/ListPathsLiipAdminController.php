<?php

declare(strict_types=1);

namespace App\Http\Admin\Controller\Developer\Liip;

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

    public function __invoke(ParameterBagInterface $parameterBag): Response
    {
        /** @var array $resolvers */
        $resolvers = $parameterBag->get('liip_imagine.resolvers');
        $webPath = $resolvers['cache_provider']['web_path'];
        $cacheRoot = $webPath['web_root'].'/'.$webPath['cache_prefix'];

        /** @var array $loaders */
        $loaders = $parameterBag->get('liip_imagine.loaders');
        $dataRoot = $loaders['default']['filesystem']['data_root'][0];

        $finder = new Finder();
        $files = $finder->files()->in($dataRoot);
        $paths = [];

        foreach ($files as $file) {
            $paths[$file->getRealPath()] = [];

            $cachedFiles = (new Finder())->files()->in($cacheRoot)->name('*'.$file->getFilename().'*');

            foreach ($cachedFiles as $cachedFile) {
                $paths[$file->getRealPath()][] = $cachedFile->getRealPath();
            }
        }

        return $this->render('admin/developer/image-cache.html.twig', [
            'paths' => $paths,
        ]);
    }
}
