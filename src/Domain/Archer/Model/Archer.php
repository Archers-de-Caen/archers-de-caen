<?php

declare(strict_types=1);

namespace App\Domain\Archer\Model;

use App\Domain\Archer\Repository\ArcherRepository;
use App\Domain\Auth\Model\AuthToken;
use App\Domain\Result\Model\Result;
use App\Domain\Result\Model\ResultBadge;
use App\Domain\Result\Model\ResultCompetition;
use App\Domain\Result\Model\ResultTeam;
use App\Infrastructure\Model\ArcherCategoryTrait;
use App\Infrastructure\Model\EmailTrait;
use App\Infrastructure\Model\FirstNameTrait;
use App\Infrastructure\Model\GenderTrait;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\LastNameTrait;
use App\Infrastructure\Model\LicenseNumberTrait;
use App\Infrastructure\Model\PhoneTrait;
use App\Infrastructure\Model\TimestampTrait;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
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
    use FirstNameTrait;
    use LastNameTrait;
    use EmailTrait;
    use PhoneTrait;
    use LicenseNumberTrait;
    use GenderTrait;
    use ArcherCategoryTrait;

    private const LICENSE_NUMBER_UNIQUE = true;
    private const EMAIL_UNIQUE = true;

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

    /**
     * @var array<string>
     */
    #[ORM\Column(type: Types::JSON)]
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

    /**
     * @var string|null Numéro d'affiliation FFTA
     */
    #[ORM\Column(type: Types::STRING, length: 14, unique: true, nullable: true)]
    #[Assert\Length(max: 14)]
    private ?string $membershipNumber = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?PostalAddress $postalAddress = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $birthdayDate = null;

    #[ORM\Column(type: Types::STRING, length: 40, nullable: true)]
    #[Assert\Length(max: 40)]
    private ?string $nationality = null;

    /**
     * @var Collection<int, AuthToken>
     */
    #[ORM\OneToMany(mappedBy: 'archer', targetEntity: AuthToken::class, orphanRemoval: true)]
    private Collection $authTokens;

    public function __construct()
    {
        $this->archerLicenses = new ArrayCollection();
        $this->resultsTeams = new ArrayCollection();
        $this->authTokens = new ArrayCollection();
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

    public function getUserIdentifier(): string
    {
        return $this->getLicenseNumber() ?? $this->getEmail() ?? '';
    }

    public function isEqualTo(UserInterface $user): bool
    {
        return method_exists($user, 'getLicenseNumber') && $this->getLicenseNumber() === $user->getLicenseNumber();
    }

    // Getter / Setter

    public function getFullName(): string
    {
        return $this->getFirstName().' '.$this->getLastName();
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

    public function updateLastLogin(): self
    {
        $this->setLastLogin(new \DateTime());

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

    public function getMembershipNumber(): ?string
    {
        return $this->membershipNumber;
    }

    public function setMembershipNumber(?string $membershipNumber): self
    {
        $this->membershipNumber = $membershipNumber;

        return $this;
    }

    public function getPostalAddress(): ?PostalAddress
    {
        return $this->postalAddress;
    }

    public function setPostalAddress(?PostalAddress $postalAddress): self
    {
        $this->postalAddress = $postalAddress;

        return $this;
    }

    public function getBirthdayDate(): ?\DateTimeInterface
    {
        return $this->birthdayDate;
    }

    public function setBirthdayDate(?\DateTimeInterface $birthdayDate): self
    {
        $this->birthdayDate = $birthdayDate;

        return $this;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(?string $nationality): void
    {
        $this->nationality = $nationality;
    }

    /**
     * @return Collection<int, AuthToken>
     */
    public function getAuthTokens(): Collection
    {
        return $this->authTokens;
    }

    /**
     * @return Collection<int, AuthToken>
     */
    public function getActiveAuthTokens(): Collection
    {
        return $this->getAuthTokens()->filter(static fn (AuthToken $authToken) => !$authToken->getUsedAt() && $authToken->getExpiredAt() > new \DateTime());
    }

    public function addAuthToken(AuthToken $authToken): self
    {
        if (!$this->authTokens->contains($authToken)) {
            $this->authTokens->add($authToken);

            if ($authToken->getArcher() !== $this) {
                $authToken->setArcher($this);
            }
        }

        return $this;
    }

    public function removeAuthToken(AuthToken $authToken): self
    {
        if ($this->authTokens->remove($authToken)) {
            $authToken->setArcher(null);
        }

        return $this;
    }
}
