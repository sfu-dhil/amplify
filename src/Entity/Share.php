<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ShareRepository;
use Doctrine\ORM\Mapping as ORM;
use Nines\UserBundle\Entity\User;
use Nines\UtilBundle\Entity\AbstractEntity;

#[ORM\Entity(repositoryClass: ShareRepository::class)]
#[ORM\UniqueConstraint(name: 'shares_uniq', columns: ['user_id', 'podcast_id'])]
class Share extends AbstractEntity {
    #[ORM\ManyToOne(targetEntity: 'Nines\UserBundle\Entity\User')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: 'Podcast', inversedBy: 'shares')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Podcast $podcast = null;

    public function __construct() {
        parent::__construct();
    }

    public function __toString() : string {
        return implode(',', [$this->user, $this->podcast]);
    }

    public function getUser() : ?User {
        return $this->user;
    }

    public function setUser(?User $user) : self {
        $this->user = $user;

        return $this;
    }

    public function getPodcast() : ?Podcast {
        return $this->podcast;
    }

    public function setPodcast(?Podcast $podcast) : self {
        $this->podcast = $podcast;

        return $this;
    }
}
