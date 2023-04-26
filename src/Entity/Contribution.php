<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ContributionRepository;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;

#[ORM\Entity(repositoryClass: ContributionRepository::class)]
class Contribution extends AbstractEntity {
    #[ORM\ManyToOne(targetEntity: 'Person', inversedBy: 'contributions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Person $person = null;

    #[ORM\ManyToOne(targetEntity: 'ContributorRole', inversedBy: 'contributions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ContributorRole $contributorRole = null;

    #[ORM\ManyToOne(targetEntity: 'Podcast', inversedBy: 'contributions')]
    private ?Podcast $podcast = null;

    #[ORM\ManyToOne(targetEntity: 'Season', inversedBy: 'contributions')]
    private ?Season $season = null;

    #[ORM\ManyToOne(targetEntity: 'Episode', inversedBy: 'contributions')]
    private ?Episode $episode = null;

    public function __construct() {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString() : string {
        return implode(',', [$this->person, $this->contributorRole, $this->podcast, $this->season, $this->episode]);
    }

    public function getPerson() : ?Person {
        return $this->person;
    }

    public function setPerson(?Person $person) : self {
        $this->person = $person;

        return $this;
    }

    public function getContributorRole() : ?ContributorRole {
        return $this->contributorRole;
    }

    public function setContributorRole(?ContributorRole $contributorRole) : self {
        $this->contributorRole = $contributorRole;

        return $this;
    }

    public function getPodcast() : ?Podcast {
        return $this->podcast;
    }

    public function setPodcast(?Podcast $podcast) : self {
        $this->podcast = $podcast;

        return $this;
    }

    public function getSeason() : ?Season {
        return $this->season;
    }

    public function setSeason(?Season $season) : self {
        $this->season = $season;

        return $this;
    }

    public function getEpisode() : ?Episode {
        return $this->episode;
    }

    public function setEpisode(?Episode $episode) : self {
        $this->episode = $episode;

        return $this;
    }
}
