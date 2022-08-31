<?php

namespace App\Http\Security\Voter;

use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Model\ArcherLicense;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ArcherLicenseVoter extends Voter
{
    public final const CREATE = 'create_archer_license';
    public final const UPDATE = 'update_archer_license';
    public final const DELETE = 'delete_archer_license';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
                self::DELETE,
                self::UPDATE,
                self::CREATE,
            ]) && (!$subject || $subject instanceof ArcherLicense);
    }

    /**
     * @param ?ArcherLicense $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof Archer) {
            return false;
        }

        return match ($attribute) {
            self::CREATE => $this->createArcherLicense($user),
            default => false,
        };
    }

    private function createArcherLicense(Archer $archer): bool
    {
        return $archer->getFirstName() &&
            $archer->getLastName() &&
            $archer->getEmail() &&
            $archer->getPhone() &&
            $archer->getGender() &&
            $archer->getPostalAddress() &&
            $archer->getBirthdayDate() &&
            $archer->getNationality();
    }
}
