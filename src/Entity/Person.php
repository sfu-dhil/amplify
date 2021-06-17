<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;
use Normalizer;

/**
 * @ORM\Entity(repositoryClass=PersonRepository::class)
 */
class Person extends AbstractEntity {
    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $fullname;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $sortableName;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $location;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $bio;

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    private $links;

    /**
     * @var Institution
     * @ORM\ManyToOne(targetEntity="App\Entity\Institution", inversedBy="people")
     */
    private $institution;

    /**
     * @var Collection|Contribution[]
     * @ORM\OneToMany(targetEntity="Contribution", mappedBy="person", cascade={"remove"})
     */
    private $contributions;

    public function __construct() {
        parent::__construct();
        $this->contributions = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
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

    public function getBio($asText = false) : ?string {
        if($asText) {
            $s = $this->bio;
            $s = strip_tags($s);
            $s = html_entity_decode($s, ENT_QUOTES | ENT_SUBSTITUTE | ENT_DISALLOWED | ENT_HTML5, 'UTF-8');
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

    public function getLinks() : ?array {
        return $this->links;
    }

    public function setLinks(array $links) : self {
        $this->links = $links;

        return $this;
    }

    /**
     * @return Collection|Contribution[]
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

    public function getInstitution() : ?Institution {
        return $this->institution;
    }

    public function setInstitution(?Institution $institution) : self {
        $this->institution = $institution;

        return $this;
    }
}
