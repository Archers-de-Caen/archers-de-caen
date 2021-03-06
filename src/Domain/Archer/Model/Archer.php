<?php

declare(strict_types=1);

namespace App\Domain\Archer\Model;

use App\Domain\Archer\Repository\ArcherRepository;
use App\Domain\Result\Model\Result;
use App\Domain\Result\Model\ResultBadge;
use App\Domain\Result\Model\ResultCompetition;
use App\Domain\Result\Model\ResultTeam;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArcherRepository::class)]
#[UniqueEntity('email')]
#[UniqueEntity('licenseNumber')]
class Archer implements UserInterface, PasswordAuthenticatedUserInterface, EquatableInterface
{
    use IdTrait;
    use TimestampTrait;

    public const ROLE_ARCHER = 'ROLE_ARCHER';
    public const ROLE_EDITOR = 'ROLE_EDITOR';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_DEVELOPER = 'ROLE_DEVELOPER';
    public const ROLES = [
        self::ROLE_ARCHER,
        self::ROLE_EDITOR,
        self::ROLE_ADMIN,
        self::ROLE_DEVELOPER,
    ];

    #[ORM\Column(type: Types::STRING, length: 191)]
    #[Assert\Length(max: 191)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    private ?string $firstName = null;

    #[ORM\Column(type: Types::STRING, length: 191)]
    #[Assert\Length(max: 191)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::STRING, length: 191, unique: true, nullable: true)]
    #[Assert\Length(max: 191)]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(type: Types::STRING, length: 12, nullable: true)]
    #[Assert\Length(max: 12)]
    private ?string $phone = null;

    #[ORM\Column(type: Types::STRING, length: 191, nullable: true)]
    #[Assert\Length(max: 191)]
    private ?string $password = null;

    #[Assert\Length(max: 255)]
    #[Assert\NotCompromisedPassword]
    private ?string $plainPassword = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $lastLogin;

    /**
     * @var Collection<int, ArcherLicense>
     */
    #[ORM\OneToMany(mappedBy: 'archer', targetEntity: ArcherLicense::class, cascade: ['ALL'], orphanRemoval: true)]
    private Collection $archerLicenses;

    #[ORM\Column(type: Types::STRING, length: 7, unique: true)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Length(max: 7)]
    #[Assert\Regex('/[0-9]{6}[A-Za-z]/')]
    private ?string $licenseNumber = null;

    /**
     * @var array<string>
     */
    #[ORM\Column(type: Types::ARRAY)]
    private array $roles = [];

    /**
     * @var Collection<int, Result|ResultBadge|ResultCompetition>
     */
    #[ORM\OneToMany(mappedBy: 'archer', targetEntity: Result::class, cascade: ['ALL'], orphanRemoval: true)]
    private Collection $results;

    /**
     * @var Collection<int, Result|ResultTeam>
     */
    #[ORM\ManyToMany(targetEntity: ResultTeam::class, mappedBy: 'teammates')]
    private Collection $resultsTeams;

    public function __construct()
    {
        $this->archerLicenses = new ArrayCollection();
        $this->pages = new ArrayCollection();
        $this->resultsTeams = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getLicenseNumber().' | '.$this->getFirstName().' '.$this->getLastName();
    }

    // UserInterface

    public function getRoles(): array
    {
        $this->addRole(self::ROLE_ARCHER);

        return array_unique($this->roles);
    }

    public function addRole(?string $role): self
    {
        if ($role && !in_array($role, $this->roles, true) && in_array($role, self::ROLES)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(string $role): self
    {
        $key = array_search($role, $this->roles, true);

        if (false !== $key) {
            unset($this->roles[$key]);
        }

        return $this;
    }

    public function eraseCredentials(): void
    {
        $this->setPlainPassword(null);
    }

    /**
     * TODO: a v??rifier.
     *
     * @throws Exception
     */
    public function getUserIdentifier(): string
    {
        if (!$this->getLicenseNumber() && !$this->getEmail()) {
            throw new Exception('L\'utilisateur doit avoir au moins son num??ro de licence ou un email');
        }

        return ($this->getLicenseNumber() ?: $this->getEmail()) ?: '';
    }

    public function isEqualTo(UserInterface $user): bool
    {
        return method_exists($user, 'getEmail') && $this->getEmail() === $user->getEmail();
    }

    // Getter / Setter

    public function getFullName(): string
    {
        return $this->getFirstName().' '.$this->getLastName();
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getLastLogin(): ?DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * @return Collection<int, ArcherLicense>
     */
    public function getArcherLicenses(): Collection
    {
        return $this->archerLicenses;
    }

    public function getArcherLicenseActive(): ?ArcherLicense
    {
        return $this->archerLicenses->filter(static fn (ArcherLicense $archerLicense): bool => (bool) $archerLicense->isActive())->first() ?: null;
    }

    public function addArcherLicense(ArcherLicense $archerLicense): self
    {
        if (!$this->archerLicenses->contains($archerLicense)) {
            $this->archerLicenses[] = $archerLicense;
            $archerLicense->setArcher($this);
        }

        return $this;
    }

    public function removeArcherLicense(ArcherLicense $archerLicense): self
    {
        if ($this->archerLicenses->removeElement($archerLicense)) {
            // set the owning side to null (unless already changed)
            if ($archerLicense->getArcher() === $this) {
                $archerLicense->setArcher(null);
            }
        }

        return $this;
    }

    public function getLicenseNumber(): ?string
    {
        return $this->licenseNumber;
    }

    public function setLicenseNumber(?string $licenseNumber): self
    {
        if ($licenseNumber) {
            $this->licenseNumber = strtoupper($licenseNumber);
        }

        return $this;
    }

    /**
     * @return Collection<int, Result|ResultBadge|ResultCompetition>
     */
    public function getResults(): Collection
    {
        return $this->results;
    }

    /**
     * @return Collection<int, Result|ResultBadge>
     */
    public function getResultsProgressArrow(): Collection
    {
        return $this->results->filter(static fn (Result $result) => $result instanceof ResultBadge && 'progress_arrow' === $result->getBadge()?->getType());
    }

    public function getBestProgressArrowObtained(): ?ResultBadge
    {
        /** @var ResultBadge[] $resultProgressArrows */
        $resultProgressArrows = $this->getResultsProgressArrow()->toArray();

        usort(
            $resultProgressArrows,
            static fn (ResultBadge $one, ResultBadge $two): int => $one->getBadge()?->getLevel() > $two->getBadge()?->getLevel() ? -1 : 1
        );

        return count($resultProgressArrows) ? $resultProgressArrows[0] : null;
    }

    /**
     * @return Collection<int, Result|ResultCompetition>
     */
    public function getResultsCompetition(): Collection
    {
        return $this->results->filter(static fn (Result $result) => $result instanceof ResultCompetition);
    }

    /**
     * @return Collection<int, Result|ResultBadge>
     */
    public function getResultsBadge(): Collection
    {
        return $this->results->filter(static fn (Result $result) => $result instanceof ResultBadge);
    }

    public function addResult(Result $result): self
    {
        if (!$this->results->contains($result)) {
            $this->results[] = $result;
            $result->setArcher($this);
        }

        return $this;
    }

    public function removeResult(Result $result): self
    {
        if ($this->results->removeElement($result)) {
            // set the owning side to null (unless already changed)
            if ($result->getArcher() === $this) {
                $result->setArcher(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Result|ResultTeam>
     */
    public function getResultsTeams(): Collection
    {
        return $this->resultsTeams;
    }

    public function addResultTeam(ResultTeam $resultTeam): self
    {
        if (!$this->resultsTeams->contains($resultTeam)) {
            $this->resultsTeams[] = $resultTeam;
            $resultTeam->addTeammate($this);
        }

        return $this;
    }

    public function removeResultTeam(ResultTeam $resultTeam): self
    {
        $this->resultsTeams->removeElement($resultTeam);

        return $this;
    }
}
