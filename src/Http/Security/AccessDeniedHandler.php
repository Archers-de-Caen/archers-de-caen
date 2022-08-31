<?php

declare(strict_types=1);

namespace App\Http\Security;

use App\Http\App\Controller\DefaultController;

use function in_array;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator, private readonly Environment $twig)
    {
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function handle(Request $request, AccessDeniedException $accessDeniedException): Response
    {
        if (!empty($accessDeniedException->getAttributes())) {
            if (in_array('application/json', $request->getAcceptableContentTypes(), true)) {
                return new JsonResponse(['message' => $accessDeniedException->getMessage()], Response::HTTP_FORBIDDEN);
            }

            $request->getSession()->getFlashBag()->add('danger', $accessDeniedException->getMessage());

            return new RedirectResponse($this->urlGenerator->generate(DefaultController::ROUTE_APP_INDEX));
        }

        if (in_array('application/json', $request->getAcceptableContentTypes(), true)) {
            return new JsonResponse(null, Response::HTTP_FORBIDDEN);
        }

        return new Response($this->twig->render('bundles/TwigBundle/Exception/error403.html.twig'), Response::HTTP_FORBIDDEN);
    }
}
