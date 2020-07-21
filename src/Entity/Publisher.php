<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PublisherRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass=PublisherRepository::class)
 */
class Publisher extends AbstractEntity {

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $location;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $website;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $contact;

    /**
     * @var Collection|Podcast[]
     * @ORM\OneToMany(targetEntity="Podcast", mappedBy="publisher")
     */
    private $podcasts;

    /**
     * @var Collection|Season[]
     * @ORM\OneToMany(targetEntity="Season", mappedBy="publisher")
     */
    private $seasons;

    /**
     * @inheritDoc
     */
    public function __toString() : string {
        // TODO: Implement __toString() method.
    }

    public function __construct() {
        parent::__construct();
    }

}
