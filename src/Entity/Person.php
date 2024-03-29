<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
#[ORM\Index(name: 'person_ft', columns: ['fullname', 'bio'], flags: ['fulltext'])]
class Person extends AbstractEntity {
    #[ORM\Column(type: 'string')]
    private ?string $fullname = null;

    #[ORM\Column(type: 'string')]
    private ?string $sortableName = null;

    #[ORM\Column(type: 'string')]
    private ?string $location = null;

    #[ORM\Column(type: 'text')]
    private ?string $bio = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $institution = null;

    #[ORM\ManyToOne(targetEntity: 'Podcast', inversedBy: 'allPeople')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Podcast $podcast = null;

    /**
     * @var Collection<int,Contribution>
     */
    #[ORM\OneToMany(targetEntity: 'Contribution', mappedBy: 'person', cascade: ['remove'])]
    private $contributions;

    public function __construct() {
        parent::__construct();
        $this->contributions = new ArrayCollection();
    }

    public function __toString() : string {
        return $this->fullname;
    }

    public function getFullname() : ?string {
        return $this->fullname;
    }

    public function setFullname(string $fullname) : self {
        $this->fullname = $fullname;

        return $this;
    }

    public function getSortableName() : ?string {
        return $this->sortableName;
    }

    public function setSortableName(string $sortableName) : self {
        $this->sortableName = $sortableName;

        return $this;
    }

    public function getLocation() : ?string {
        return $this->location;
    }

    public function setLocation(string $location) : self {
        $this->location = $location;

        return $this;
    }

    public function getBio(bool $asText = false) : ?string {
        if ($asText) {
            $s = $this->bio;
            $s = strip_tags($s);
            $s = html_entity_decode($s);
            $s = str_replace(["\r\n", "\r", "\n"], "\n", $s);
            $s = preg_replace("/\n{3,}/", "\n\n", $s);
            $s = preg_replace('/[^\S\n]+/u', ' ', $s);

            return preg_replace('/^\s+|\s+$/u', '', $s);
        }

        return $this->bio;
    }

    public function setBio(string $bio) : self {
        $this->bio = $bio;

        return $this;
    }

    public function getInstitution() : ?string {
        return $this->institution;
    }

    public function setInstitution(?string $institution) : self {
        $this->institution = $institution;

        return $this;
    }

    public function getPodcast() : ?Podcast {
        return $this->podcast;
    }

    public function setPodcast(?Podcast $podcast) : self {
        $this->podcast = $podcast;

        return $this;
    }

    /**
     * @return Collection<int,Contribution>
     */
    public function getContributions() : Collection {
        return $this->contributions;
    }

    public function addContribution(Contribution $contribution) : self {
        if ( ! $this->contributions->contains($contribution)) {
            $this->contributions[] = $contribution;
            $contribution->setPerson($this);
        }

        return $this;
    }

    public function removeContribution(Contribution $contribution) : self {
        if ($this->contributions->contains($contribution)) {
            $this->contributions->removeElement($contribution);
            // set the owning side to null (unless already changed)
            if ($contribution->getPerson() === $this) {
                $contribution->setPerson(null);
            }
        }

        return $this;
    }
}
