<?php

declare(strict_types=1);

namespace App\Domain\Archer\Model;

use App\Domain\Billing\Config\PaymentMethod;
use App\Domain\Archer\Repository\ArcherLicenseRepository;
use App\Domain\File\Model\Document;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArcherLicenseRepository::class)]
class ArcherLicense
{
    use IdTrait;
    use TimestampTrait;

    #[ORM\ManyToOne(targetEntity: Archer::class, inversedBy: 'archerLicenses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Archer $archer = null;

    #[ORM\ManyToOne(targetEntity: License::class, inversedBy: 'archerLicenses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?License $license = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $dateStart;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $dateEnd;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private ?bool $active = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private ?bool $payed = false;

    #[ORM\Column]
    private ?bool $fftaAttachedNoticeRead = null;

    #[ORM\Column]
    private ?bool $individualInsurance = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private array $weapons = [];

    #[ORM\Column]
    private ?bool $fftaNewsletter = null;

    #[ORM\Column]
    private ?bool $photoUse = null;

    #[ORM\Column(nullable: true)]
    private array $contacts = [];

    #[ORM\Column]
    private ?bool $runArchery = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Document $mainMedicalCertificate = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $mainMedicalCertificateType = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Document $runArcheryMedicalCertificate = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $runArcheryMedicalCertificateType = null;

    #[ORM\Column(length: 191, nullable: true, enumType: PaymentMethod::class)]
    private ?PaymentMethod $paymentMethod = null;

    #[ORM\Column(type: Types::JSON)]
    private array $payment = [];

    public function getArcher(): ?Archer
    {
        return $this->archer;
    }

    public function getDateStart(): ?DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setDateStart(?DateTimeInterface $dateStart): self
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(?DateTimeInterface $dateEnd): self
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function setArcher(?Archer $archer): self
    {
        $this->archer = $archer;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        if ($active) {
            foreach ($this->getArcher() ? $this->getArcher()->getArcherLicenses() : [] as $archerLicense) {
                $archerLicense->setActive(false);
            }
        }

        $this->active = $active;

        return $this;
    }

    public function getLicense(): ?License
    {
        return $this->license;
    }

    public function setLicense(?License $license): self
    {
        $this->license = $license;

        return $this;
    }

    public function getPayed(): ?bool
    {
        return $this->payed;
    }

    public function setPayed(?bool $payed): self
    {
        $this->payed = $payed;

        return $this;
    }

    public function isFftaAttachedNoticeRead(): ?bool
    {
        return $this->fftaAttachedNoticeRead;
    }

    public function setFftaAttachedNoticeRead(bool $fftaAttachedNoticeRead): self
    {
        $this->fftaAttachedNoticeRead = $fftaAttachedNoticeRead;

        return $this;
    }

    public function isIndividualInsurance(): ?bool
    {
        return $this->individualInsurance;
    }

    public function setIndividualInsurance(bool $individualInsurance): self
    {
        $this->individualInsurance = $individualInsurance;

        return $this;
    }

    public function getWeapons(): array
    {
        return $this->weapons;
    }

    public function setWeapons(array $weapons): self
    {
        $this->weapons = $weapons;

        return $this;
    }

    public function isFftaNewsletter(): ?bool
    {
        return $this->fftaNewsletter;
    }

    public function setFftaNewsletter(bool $fftaNewsletter): self
    {
        $this->fftaNewsletter = $fftaNewsletter;

        return $this;
    }

    public function isPhotoUse(): ?bool
    {
        return $this->photoUse;
    }

    public function setPhotoUse(bool $photoUse): self
    {
        $this->photoUse = $photoUse;

        return $this;
    }

    public function getContacts(): array
    {
        return $this->contacts;
    }

    public function setContacts(array $contacts): self
    {
        $this->contacts = $contacts;

        return $this;
    }

    public function getPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?PaymentMethod $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function isRunArchery(): ?bool
    {
        return $this->runArchery;
    }

    public function setRunArchery(bool $runArchery): self
    {
        $this->runArchery = $runArchery;

        return $this;
    }

    public function getMainMedicalCertificate(): ?Document
    {
        return $this->mainMedicalCertificate;
    }

    public function setMainMedicalCertificate(?Document $mainMedicalCertificate): self
    {
        $this->mainMedicalCertificate = $mainMedicalCertificate;

        return $this;
    }

    public function getMainMedicalCertificateType(): ?string
    {
        return $this->mainMedicalCertificateType;
    }

    public function setMainMedicalCertificateType(?string $mainMedicalCertificateType): void
    {
        $this->mainMedicalCertificateType = $mainMedicalCertificateType;
    }

    public function getRunArcheryMedicalCertificate(): ?Document
    {
        return $this->runArcheryMedicalCertificate;
    }

    public function setRunArcheryMedicalCertificate(?Document $runArcheryMedicalCertificate): void
    {
        $this->runArcheryMedicalCertificate = $runArcheryMedicalCertificate;
    }

    public function getRunArcheryMedicalCertificateType(): ?string
    {
        return $this->runArcheryMedicalCertificateType;
    }

    public function setRunArcheryMedicalCertificateType(?string $runArcheryMedicalCertificateType): void
    {
        $this->runArcheryMedicalCertificateType = $runArcheryMedicalCertificateType;
    }

    public function getPayment(): array
    {
        return $this->payment;
    }

    public function setPayment(array $payment): void
    {
        $this->payment = $payment;
    }
}
