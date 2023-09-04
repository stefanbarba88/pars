<?php

namespace App\Security\Voter;

use App\Classes\Data\UserRolesData;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends Voter {
    public const EDIT = 'USER_EDIT';
    public const VIEW = 'USER_VIEW';
    public const DELETE = 'USER_DELETE';

    public function __construct(private readonly Security $security, private readonly LoggerInterface $logger) {
    }

    protected function supports(string $attribute, mixed $subject): bool {
        // https://symfony.com/doc/current/security/voters.html
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])) {
            return false;
        }

        // only vote on `Post` objects
        if (!$subject instanceof User) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool {
        $user = $token->getUser();

//     if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        /*
         * Super admin moze sve i odmah se setuje da ima dozvolu
         */
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        if ($this->security->isGranted('ROLE_UPRAVNIK_CENTRALE')) {
            if (!is_null($subject->getGrana())) {
                return false;
            }
        }

        if ($this->security->isGranted('ROLE_UPRAVNIK_GRANE')) {
            if (is_null($subject->getGrana()) || ($subject->getGrana()->getId() != $user->getGranskiSindikatUpGrana()->getId())) {
                return false;
            }
        }

        $korisnik = $subject;

        return match($attribute) {
            self::VIEW => $this->canView($korisnik, $user),
            self::EDIT => $this->canEdit($korisnik, $user),
            self::DELETE => $this->canDelete($korisnik, $user),
        };
    }

    private function canEdit(User $korisnik, User $user): bool {

        if ($this->security->isGranted('ROLE_UPRAVNIK_CENTRALE'))  {
            if ($korisnik->getUserType() == UserRolesData::ROLE_REG_POVERENIK) {
                return true;
            }
        }

        if ($this->security->isGranted('ROLE_UPRAVNIK_GRANE')) {
            if ($korisnik->getUserType() == UserRolesData::ROLE_POVERENIK || $korisnik->getUserType() == UserRolesData::ROLE_REG_POVERENIK) {
                return true;
            }
        }
        return false;

    }

    private function canView(User $korisnik, User $user): bool {
        if ($this->security->isGranted('ROLE_UPRAVNIK_CENTRALE'))  {
            if ($korisnik->getUserType() == UserRolesData::ROLE_REG_POVERENIK) {
                return true;
            }
        }

        if ($this->security->isGranted('ROLE_UPRAVNIK_GRANE')) {
            if ($korisnik->getUserType() == UserRolesData::ROLE_POVERENIK || $korisnik->getUserType() == UserRolesData::ROLE_REG_POVERENIK) {
                return true;
            }
        }
        return false;
    }

    private function canDelete(User $korisnik, User $user): bool {
        if ($this->security->isGranted('ROLE_UPRAVNIK_CENTRALE')) {
            if ($korisnik->getUserType() == UserRolesData::ROLE_REG_POVERENIK) {
                return true;
            }
        }

        if ($this->security->isGranted('ROLE_UPRAVNIK_GRANE')) {
            if ($korisnik->getUserType() == UserRolesData::ROLE_POVERENIK || $korisnik->getUserType() == UserRolesData::ROLE_REG_POVERENIK) {
                return true;
            }
        }
        return false;
    }
}
