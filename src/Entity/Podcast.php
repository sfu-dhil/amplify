<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PodcastRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass=PodcastRepository::class)
 */
class Podcast extends AbstractEntity {

    /**
     * @var Publisher
     * @ORM\ManyToOne(targetEntity="Publisher", inversedBy="podcasts")
     */
    private $publisher;

    /**
     * @var Collection|Contribution[]
     * @ORM\OneToMany(targetEntity="Contribution", mappedBy="podcast")
     */
    private $contributions;

    /**
     * @var Collection|Season[]
     * @ORM\OneToMany(targetEntity="Season", mappedBy="podcast")
     */
    private $seasons;

    /**
     * @var Collection|Episode[]
     * @ORM\OneToMany(targetEntity="Episode", mappedBy="podcast")
     */
    private $episodes;

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
