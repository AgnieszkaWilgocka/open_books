<?php

namespace App\Security\Voter;

use App\Entity\Rental;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class RentalVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const RENTAL_RETURN = 'RENTAL_RETURN';

    public function __construct(
        private AccessDecisionManagerInterface $accessDecisionManager,
    ) {
    }
    
    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html

        return in_array($attribute, [self::VIEW, self::RENTAL_RETURN])
                && $subject instanceof Rental;
           
        
        // if ($subject instanceof Rental) {
        //     return false;
        // }

        // return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            $vote?->addReason('The user is not logged in.');
            return false;
        }

        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        /** @var Rental $rental */
        $rental = $subject;


        // ... (check conditions and return true to grant permission) ...
        return match ($attribute) {
            self::VIEW => $this->canView($user, $rental),
            self::RENTAL_RETURN => $this->canReturn($user, $rental, $vote),
        };
    }

    private function canView(User $user, Rental $rental): bool {
        return $rental->getOwner() == $user;
    }

    private function canReturn(User $user, Rental $rental, ?Vote $vote): bool {
        // return $rental->getOwner() == $user;
        if ($rental->getOwner() == $user) {
            return true;
        }

        $vote?->addReason(sprintf(
            'The logged in user (username: %s) is not the author of this post (id: %d).',
            $user->getEmail(), $rental->getId()
        ));

        return false;
    }
}
