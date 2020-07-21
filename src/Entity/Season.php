<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SeasonRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass=SeasonRepository::class)
 */
class Season extends AbstractEntity {

    /**
     * @var Podcast
     * @ORM\ManyToOne(targetEntity="Podcast", inversedBy="seasons")
     * @ORM\JoinColumn(nullable=false)
     */
    private $podcast;

    /**
     * @var Publisher
     * @ORM\ManyToOne(targetEntity="Publisher", inversedBy="seasons")
     * @ORM\JoinColumn(nullable=true)
     */
    private $publisher;

    /**
     * @var Collection|Contribution[]
     * @ORM\OneToMany(targetEntity="Contribution", mappedBy="season")
     */
    private $contributions;

    /**
     * @var Collection|Episode[]
     * @ORM\OneToMany(targetEntity="Episode", mappedBy="season")
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
