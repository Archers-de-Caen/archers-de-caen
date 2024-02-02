<?php

declare(strict_types=1);

namespace App\Http\Webhook\Controller;

use App\Domain\Competition\Model\CompetitionRegisterDepartureTargetArcher;
use App\Domain\Webhook\Webhook;
use Doctrine\ORM\EntityManagerInterface;
use Helloasso\Exception\InvalidValueException;
use Helloasso\HelloassoClient;
use Helloasso\Models\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[AsController]
#[Route(
    path: '/helloasso',
    name: self::ROUTE,
    methods: Request::METHOD_POST
)]
class HelloassoWebhookController extends AbstractController
{
    public const ROUTE = 'webhook_helloasso';

    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function __invoke(Request $request, ParameterBagInterface $parameterBag): Response
    {
        $parameter = $parameterBag->all();
        $helloasso = new HelloassoClient(
            $parameter['hello_asso_id'],
            $parameter['hello_asso_secret'],
            $parameter['hello_asso_organization_name'],
            true
        );

        $webhook = (new Webhook())->setService(Webhook::SERVICE_HELLOASSO);

        $content = $request->getContent();

        if ($content) {
            try {
                $event = $helloasso->event->decode($content);
                $webhook
                    ->setContent((array) json_decode($content, true, 512, \JSON_THROW_ON_ERROR))
                    ->setType($event->getEventType())
                ;

                $msg = match ($event->getEventType()) {
                    Event::EVENT_TYPE_ORDER => $this->order(),
                    Event::EVENT_TYPE_PAYMENT => $this->payment($event),
                    Event::EVENT_TYPE_FORM => $this->form(),
                    default => $event->getEventType().' not implemented',
                };
            } catch (InvalidValueException|\JsonException $e) {
                $msg = $e->getMessage();
            }
        } else {
            $msg = 'No content in webhook request';
        }

        $webhook->setResult($msg);

        $this->em->persist($webhook);

        $this->em->flush();

        return $this->json('OK');
    }

    private function order(): string
    {
        return 'Order type not implemented';
    }

    private function payment(Event $event): string
    {
        if (!$registrationIds = ($event->getMetadata()['registrations'] ?? null)) {
            return 'Only registrations payments implemented';
        }

        $registrations = $this->em->getRepository(CompetitionRegisterDepartureTargetArcher::class)->findBy([
            'id' => array_map(static fn (string $id) => Uuid::fromString($id)->toBinary(), $registrationIds),
        ]);

        foreach ($registrations as $registration) {
            $registration->setPaid(true);
        }

        return \count($registrations).' registration paid ('.implode(', ', $registrationIds).')';
    }

    private function form(): string
    {
        return 'Form type not implemented';
    }
}
