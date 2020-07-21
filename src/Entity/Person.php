<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;

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
    private $affiliation;

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
     * @var string
     * @ORM\Column(type="array")
     */
    private $links;

    /**
     * @var Collection|Contribution[]
     * @ORM\OneToMany(targetEntity="Contribution", mappedBy="person")
     */
    private $contributions;

    /**
     * @inheritDoc
     */
    public function __toString() : string {
        // TODO: Implement __toString() method.
    }

    public function __construct() {
        parent::__construct();
        $this->contributions = new ArrayCollection();
    }

}
