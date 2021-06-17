<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Entity;

use App\Repository\ContributorRoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractTerm;

/**
 * @ORM\Entity(repositoryClass=ContributorRoleRepository::class)
 */
class ContributorRole extends AbstractTerm {
    /**
     * @var string
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $relatorTerm;

    /**
     * @var Collection|Contribution[]
     * @ORM\OneToMany(targetEntity="Contribution", mappedBy="contributorRole")
     */
    private $contributions;

    public function __construct() {
        parent::__construct();
        $this->contributions = new ArrayCollection();
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

    public function getRelatorTerm(): ?string
    {
        return $this->relatorTerm;
    }

    public function setRelatorTerm(?string $relatorTerm): self
    {
        $this->relatorTerm = $relatorTerm;

        return $this;
    }
}
