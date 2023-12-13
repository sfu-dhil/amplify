<?php

declare(strict_types=1);

namespace App\Entity;

use App\Config\ContributorRole;
use App\Repository\ContributionRepository;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;

#[ORM\Entity(repositoryClass: ContributionRepository::class)]
class Contribution extends AbstractEntity {
    #[ORM\Column(type: 'json', options: ['default' => '[]'])]
    private ?array $roles = [];

    #[ORM\ManyToOne(targetEntity: 'Person', inversedBy: 'contributions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Person $person = null;

    #[ORM\ManyToOne(targetEntity: 'Podcast', inversedBy: 'contributions')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Podcast $podcast = null;

    #[ORM\ManyToOne(targetEntity: 'Season', inversedBy: 'contributions')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Season $season = null;

    #[ORM\ManyToOne(targetEntity: 'Episode', inversedBy: 'contributions')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Episode $episode = null;

    public function __construct() {
        parent::__construct();
    }

    public function __toString() : string {
        return implode(',', array_merge([$this->person, $this->podcast, $this->season, $this->episode], $this->getRoleLabels()));
    }

    public function getPerson() : ?Person {
        return $this->person;
    }

    public function setPerson(?Person $person) : self {
        $this->person = $person;

        return $this;
    }

    public function getRoles() : array {
        $roles = [];
        foreach ($this->roles as $role) {
            $roles[] = ContributorRole::from($role);
        }

        return $roles;
    }

    public function getRoleLabels() : array {
        $roles = [];
        foreach ($this->roles as $role) {
            $roles[] = ContributorRole::from($role)->label();
        }

        return $roles;
    }

    public function getRoleValues() : array {
        $roles = [];
        foreach ($this->roles as $role) {
            $roles[] = ContributorRole::from($role)->value;
        }

        return $roles;
    }

    public function setRoles(array $roles) : self {
        $this->roles = [];
        foreach ($roles as $contributorRole) {
            $this->roles[] = $contributorRole->value;
        }

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
