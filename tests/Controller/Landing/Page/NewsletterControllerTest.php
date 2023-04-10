<?php

namespace App\Tests\Controller\Landing\Page;

use App\Domain\Archer\Model\Archer;
use App\Domain\Newsletter\NewsletterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
class NewsletterControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $this->em = $em;

    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->client);
    }

    public function testNewsletterPageLoadSuccessfully(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/newsletter');

        self::assertResponseIsSuccessful();

        $title = $crawler
            ->filter('h1.title')
            ->eq(0)
            ->text()
        ;

        self::assertSame('Newsletter', $title);
    }

    public function testNewsletterSendSubscriptionFormSuccessfully(): void
    {
        $this->client->request(Request::METHOD_GET, '/newsletter');

        $crawler = $this->client->submitForm("S'inscrire", [
            'newsletter_form[types][0]' => NewsletterType::ACTUALITY_NEW->value,
            'newsletter_form[email]' => 'cleo.payne@example.com',
            'newsletter_form[licenseNumber]' => '123456A',
        ]);

        $flashElement = $crawler
            ->filter('input.flash')
            ->eq(0)
        ;

        self::assertSame('Vous êtes bien inscrit à la newsletter.', $flashElement->attr('value'));
        self::assertSame('success', $flashElement->attr('data-type'));

        /** @var Archer $archer */
        $archer = $this->em->getRepository(Archer::class)->findOneBy([
            'email' => 'cleo.payne@example.com',
            'licenseNumber' => '123456A'
        ]);

        self::assertCount(1, $archer->getNewsletters());
        self::assertSame(NewsletterType::ACTUALITY_NEW, $archer->getNewsletters()[0]);
    }

    public function testNewsletterSendUnsubscriptionFormSuccessfully(): void
    {
        $this->client->request(Request::METHOD_GET, '/newsletter');

        $crawler = $this->client->submitForm("S'inscrire", [
            'newsletter_form[email]' => 'cleo.payne@example.com',
            'newsletter_form[licenseNumber]' => '123456A',
        ]);

        $flashElement = $crawler
            ->filter('input.flash')
            ->eq(0)
        ;

        self::assertSame('Votre désinscription a été prise en compte.', $flashElement->attr('value'));
        self::assertSame('success', $flashElement->attr('data-type'));

        /** @var Archer $archer */
        $archer = $this->em->getRepository(Archer::class)->findOneBy([
            'email' => 'cleo.payne@example.com',
            'licenseNumber' => '123456A'
        ]);

        self::assertCount(0, $archer->getNewsletters());
    }

    public function testNewsletterSendAExistingLicenseNumberButWithAnotherEmail(): void
    {
        $this->client->request(Request::METHOD_GET, '/newsletter');

        /** @var Archer $archer */
        $archer = $this->em->getRepository(Archer::class)->findOneBy([]);

        $this->client->submitForm("S'inscrire", [
            'newsletter_form[email]' => 'hugh.berry@example.com',
            'newsletter_form[licenseNumber]' => $archer->getLicenseNumber(),
        ]);

        $crawler = $this->client->followRedirect();

        $flashElement = $crawler
            ->filter('input.flash')
            ->eq(0)
        ;

        self::assertSame('Ce numéro de licence est associé à une adresse email différente !', $flashElement->attr('value'));
        self::assertSame('error', $flashElement->attr('data-type'));
    }
}
