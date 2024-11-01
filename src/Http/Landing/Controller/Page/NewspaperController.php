<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Page;

use App\Domain\File\Config\DocumentType;
use App\Domain\File\Model\NewspaperAccess;
use App\Domain\File\Repository\DocumentRepository;
use App\Domain\File\Service\NewspaperAccessService;
use App\Http\Landing\Form\NewspapersAccessForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(
    path: '/gazettes',
    name: self::ROUTE,
    options: ['sitemap' => true],
    methods: [Request::METHOD_GET, Request::METHOD_POST],
)]
final class NewspaperController extends AbstractController
{
    public const string ROUTE = 'landing_newspapers';

    public function __construct(
        private readonly DocumentRepository $documentRepository,
        private readonly NewspaperAccessService $newspaperAccessService,
    ) {
    }

    public function __invoke(
        Request $request,
        #[MapQueryParameter('password')]
        ?string $password = null,
    ): Response {
        $newspapers = [];
        $accessGranted = false;

        $form = $this->createForm(NewspapersAccessForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var string $email */
                $email = $form->get('email')->getData();

                $this->newspaperAccessService->createNewspaperAccess($email);
            } catch (\Exception|ExceptionInterface) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création de votre demande.');

                return $this->redirectToRoute(self::ROUTE);
            }

            $this->addFlash('success', 'Demande de gazettes enregistrée. Vous allez recevoir un email avec votre accès.');

            return $this->redirectToRoute(self::ROUTE);
        }

        if ($password) {
            $newspaperAccess = $this->newspaperAccessService->getNewspaperAccessByPassword($password);

            if ($newspaperAccess instanceof NewspaperAccess) {
                $accessGranted = true;

                $newspapers = $this->documentRepository->findBy([
                    'type' => DocumentType::NEWSPAPER->value,
                ], [
                    'createdAt' => 'DESC',
                ]);
            }
        }

        return $this->render('/landing/newspapers/newspapers.html.twig', [
            'newspapers' => $newspapers,
            'accessGranted' => $accessGranted,
            'form' => $form->createView(),
        ]);
    }
}
