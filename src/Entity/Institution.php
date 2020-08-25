<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InstitutionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass=InstitutionRepository::class)
 * @ORM\Table(uniqueConstraints={
 *   @ORM\UniqueConstraint(name="institutions_uniq", columns={"province", "name"})
 * })
 */
class Institution extends AbstractEntity {

    /**
     * @var string
     * @ORM\Column(type="string", length=40, nullable=false)
     */
    private $province;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var Collection|Person[]
     * @ORM\OneToMany(targetEntity="App\Entity\Person", mappedBy="institution")
     */
    private $people;

    /**
     * @inheritDoc
     */
    public function __toString() : string {
        return $this->name;
    }

    public function __construct() {
        parent::__construct();
        $this->people = new ArrayCollection();
    }

    public function getProvince(): ?string
    {
        return $this->province;
    }

    public function setProvince(string $province): self
    {
        $this->province = $province;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Person[]
     */
    public function getPeople(): Collection
    {
        return $this->people;
    }

    public function addPerson(Person $person): self
    {
        if (!$this->people->contains($person)) {
            $this->people[] = $person;
            $person->setInstitution($this);
        }

        return $this;
    }

    public function removePerson(Person $person): self
    {
        if ($this->people->contains($person)) {
            $this->people->removeElement($person);
            // set the owning side to null (unless already changed)
            if ($person->getInstitution() === $this) {
                $person->setInstitution(null);
            }
        }

        return $this;
    }

}
