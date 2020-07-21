<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EpisodeRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass=EpisodeRepository::class)
 */
class Episode extends AbstractEntity {

    /**
     * @var Season|null
     * @ORM\ManyToOne(targetEntity="Season", inversedBy="episodes")
     * @ORM\JoinColumn(nullable=true)
     */
    private $season;

    /**
     * @var Podcast
     * @ORM\ManyToOne(targetEntity="Podcast", inversedBy="episodes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $podcast;

    /**
     * @var Collection|Contribution[]
     * @ORM\OneToMany(targetEntity="Contribution", mappedBy="episode")
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
    }

}
