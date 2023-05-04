<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PublisherRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;

#[ORM\Entity(repositoryClass: PublisherRepository::class)]
#[ORM\Index(name: 'publisher_ft', columns: ['name', 'description'], flags: ['fulltext'])]
class Publisher extends AbstractEntity {
    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $website = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\Column(type: 'text')]
    private ?string $contact = null;

    /**
     * @var Collection<int,Podcast>
     */
    #[ORM\OneToMany(targetEntity: 'Podcast', mappedBy: 'publisher')]
    private $podcasts;

    /**
     * @var Collection<int,Season>
     */
    #[ORM\OneToMany(targetEntity: 'Season', mappedBy: 'publisher')]
    private $seasons;

    public function __construct() {
        parent::__construct();
        $this->podcasts = new ArrayCollection();
        $this->seasons = new ArrayCollection();
    }

    public function __toString() : string {
        return $this->name;
    }

    public function getName() : ?string {
        return $this->name;
    }

    public function setName(string $name) : self {
        $this->name = $name;

        return $this;
    }

    public function getLocation() : ?string {
        return $this->location;
    }

    public function setLocation(?string $location) : self {
        $this->location = $location;

        return $this;
    }

    public function getWebsite() : ?string {
        return $this->website;
    }

    public function setWebsite(?string $website) : self {
        $this->website = $website;

        return $this;
    }

    public function getDescription() : ?string {
        return $this->description;
    }

    public function setDescription(string $description) : self {
        $this->description = $description;

        return $this;
    }

    public function getContact() : ?string {
        return $this->contact;
    }

    public function setContact(string $contact) : self {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @return Collection<int,Podcast>
     */
    public function getPodcasts() : Collection {
        return $this->podcasts;
    }

    public function addPodcast(Podcast $podcast) : self {
        if ( ! $this->podcasts->contains($podcast)) {
            $this->podcasts[] = $podcast;
            $podcast->setPublisher($this);
        }

        return $this;
    }

    public function removePodcast(Podcast $podcast) : self {
        if ($this->podcasts->contains($podcast)) {
            $this->podcasts->removeElement($podcast);
            // set the owning side to null (unless already changed)
            if ($podcast->getPublisher() === $this) {
                $podcast->setPublisher(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int,Season>
     */
    public function getSeasons() : Collection {
        return $this->seasons;
    }

    public function addSeason(Season $season) : self {
        if ( ! $this->seasons->contains($season)) {
            $this->seasons[] = $season;
            $season->setPublisher($this);
        }

        return $this;
    }

    public function removeSeason(Season $season) : self {
        if ($this->seasons->contains($season)) {
            $this->seasons->removeElement($season);
            // set the owning side to null (unless already changed)
            if ($season->getPublisher() === $this) {
                $season->setPublisher(null);
            }
        }

        return $this;
    }
}
