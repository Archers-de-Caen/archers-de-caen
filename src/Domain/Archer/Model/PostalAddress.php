<?php

declare(strict_types=1);

namespace App\Domain\Archer\Model;

use App\Domain\Archer\Repository\PostalAddressRepository;
use App\Infrastructure\Model\IdTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostalAddressRepository::class)]
class PostalAddress
{
    use IdTrait;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $line1 = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $line2 = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $line3 = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $county = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $postcode = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $otherAddressDetails = null;

    public function getLine1(): ?string
    {
        return $this->line1;
    }

    public function setLine1(?string $line1): self
    {
        $this->line1 = $line1;

        return $this;
    }

    public function getLine2(): ?string
    {
        return $this->line2;
    }

    public function setLine2(string $line2): self
    {
        $this->line2 = $line2;

        return $this;
    }

    public function getLine3(): ?string
    {
        return $this->line3;
    }

    public function setLine3(?string $line3): self
    {
        $this->line3 = $line3;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCounty(): ?string
    {
        return $this->county;
    }

    public function setCounty(?string $county): self
    {
        $this->county = $county;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(?string $postcode): self
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getOtherAddressDetails(): ?string
    {
        return $this->otherAddressDetails;
    }

    public function setOtherAddressDetails(?string $otherAddressDetails): self
    {
        $this->otherAddressDetails = $otherAddressDetails;

        return $this;
    }
}
