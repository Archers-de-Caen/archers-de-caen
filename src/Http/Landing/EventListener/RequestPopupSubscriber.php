<?php

declare(strict_types=1);

namespace App\Http\Landing\EventListener;

use App\Domain\Cms\Model\Data;
use App\Domain\Cms\Repository\DataRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;

final readonly class RequestPopupSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DataRepository $dataRepository,
        private RouterInterface $router,
    ) {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!str_starts_with($this->router->match($event->getRequest()->getPathInfo())['_route'], 'landing')) {
            return;
        }

        $data = $this->dataRepository->findByCode(Data::CODE_POPUP);

        if (!$data instanceof Data || !($content = $data->getContent())) {
            return;
        }

        $content = $content[array_key_first($content)];

        if (!($content['enable'] ?? false)) {
            return;
        }

        $updatedAt = $data->getUpdatedAt()?->format('Y-m-d H:i:s');

        $content['date'] = $updatedAt;

        $request = $event->getRequest();
        $cookie = $request->cookies->get('popup');

        if ($cookie && $cookie === $updatedAt) {
            return;
        }

        /** @var FlashBagAwareSessionInterface $session */
        $session = $request->getSession();

        try {
            $session->getFlashBag()->add('popup', json_encode($content, \JSON_THROW_ON_ERROR));

            $event->getRequest()->attributes->set('popup', $updatedAt);
        } catch (\JsonException) {
            // Do nothing
        }
    }
}
