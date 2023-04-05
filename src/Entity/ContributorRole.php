<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ContributorRoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractTerm;

#[ORM\Entity(repositoryClass: ContributorRoleRepository::class)]
class ContributorRole extends AbstractTerm {
    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $relatorTerm = null;

    /**
     * @var Collection<int,Contribution>
     */
    #[ORM\OneToMany(targetEntity: 'Contribution', mappedBy: 'contributorRole')]
    private $contributions;

    public function __construct() {
        parent::__construct();
        $this->contributions = new ArrayCollection();
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
            $contribution->setContributorRole($this);
        }

        return $this;
    }

    public function removeContribution(Contribution $contribution) : self {
        if ($this->contributions->contains($contribution)) {
            $this->contributions->removeElement($contribution);
            // set the owning side to null (unless already changed)
            if ($contribution->getContributorRole() === $this) {
                $contribution->setContributorRole(null);
            }
        }

        return $this;
    }

    public function getRelatorTerm() : ?string {
        return $this->relatorTerm;
    }

    public function setRelatorTerm(?string $relatorTerm) : self {
        $this->relatorTerm = $relatorTerm;

        return $this;
    }
}
