<?php

declare(strict_types=1);

namespace App\Infrastructure\Mailing;

use App\Domain\Archer\Repository\ArcherRepository;
use App\Domain\Cms\Repository\GalleryRepository;
use App\Domain\Cms\Repository\PageRepository;
use App\Domain\Competition\Repository\CompetitionRepository;
use App\Domain\Newsletter\Newsletter;
use App\Domain\Newsletter\NewsletterRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class NewsletterHandler
{
    public function __construct(
        private NewsletterRepository $newsletterRepository,
        private ArcherRepository $archerRepository,
        private GalleryRepository $galleryRepository,
        private PageRepository $pageRepository,
        private CompetitionRepository $competitionRepository,
        private Mailer $mailer,
    ) {
    }

    /**
     * @throws EmailRenderingException
     */
    public function __invoke(NewsletterMessage $newsletterMessage): void
    {
        /** @var array<string> $emails */
        $emails = $this->archerRepository
            ->createQueryBuilder('archer')
            ->select('archer.email')
            ->where('archer.newsletters LIKE :newsletters')
            ->setParameter('newsletters', '%'.$newsletterMessage->getType()->value.'%')
            ->getQuery()
            ->getSingleColumnResult()
        ;

        $newsletter = (new Newsletter())
            ->setEmails($emails)
            ->setContext($newsletterMessage->getContext())
            ->setType($newsletterMessage->getType())
        ;

        $this->newsletterRepository->save($newsletter, true);

        $email = $this->mailer->createEmail(
            $newsletterMessage->getType()->emailTemplatePath(),
            $this->getEmailTemplateData($newsletterMessage)
        );

        $email
            ->subject($newsletterMessage->getType()->emailSubject())
            ->addBcc(...$emails)
        ;

        $this->mailer->send($email);
    }

    private function getEmailTemplateData(NewsletterMessage $newsletterMessage): array
    {
        if ($newsletterMessage instanceof GalleryNewsletterMessage) {
            return [
                'gallery' => $this->galleryRepository->find($newsletterMessage->getGalleryUid()),
            ];
        }

        if ($newsletterMessage instanceof ActualityNewsletterMessage) {
            return [
                'actuality' => $this->pageRepository->find($newsletterMessage->getActualityUid()),
            ];
        }

        if ($newsletterMessage instanceof MonthlyReportNewsletterMessage) {
            return [
                'actualities' => $this->pageRepository->findBy(['id' => $newsletterMessage->getActualityUuids()]),
                'competitions' => $this->competitionRepository->findBy(['id' => $newsletterMessage->getCompetitionUuids()]),
                'galleries' => $this->galleryRepository->findBy(['id' => $newsletterMessage->getGalerieUuids()]),
            ];
        }

        return [];
    }
}
