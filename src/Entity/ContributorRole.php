<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ContributorRoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;
use Nines\UtilBundle\Entity\AbstractTerm;

/**
 * @ORM\Entity(repositoryClass=ContributorRoleRepository::class)
 */
class ContributorRole extends AbstractTerm {

    /**
     * @var Collection|Contribution[]
     * @ORM\OneToMany(targetEntity="Contribution", mappedBy="contributorRole")
     */
    private $contributions;

    public function __construct() {
        parent::__construct();
        $this->contributions = new ArrayCollection();
    }

}
