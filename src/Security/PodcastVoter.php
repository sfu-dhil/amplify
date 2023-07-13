<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Podcast;
use LogicException;
use Nines\UserBundle\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PodcastVoter extends Voter {
    public const ACCESS = 'access';

    public function __construct(
        private Security $security,
    ) {
    }

    private function canAccess(Podcast $podcast, User $user) : bool {
        foreach ($podcast->getShares() as $share) {
            if ($share->getUser() === $user) {
                return true;
            }
        }

        return false;
    }

    protected function supports(string $attribute, mixed $subject) : bool {
        if ( ! in_array($attribute, [self::ACCESS], true)) {
            return false;
        }

        if ( ! $subject instanceof Podcast) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token) : bool {
        $user = $token->getUser();

        if ( ! $user instanceof User) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        /** @var Podcast $podcast */
        $podcast = $subject;

        return match ($attribute) {
            self::ACCESS => $this->canAccess($podcast, $user),
            default => throw new LogicException('This code should not be reached!')
        };
    }
}
