<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EpisodeRepository;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass=EpisodeRepository::class)
 */
class Episode extends AbstractEntity {

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * @var DateTime
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * Run time in seconds.
     *
     * @var integer
     * @ORM\Column(type="integer")
     */
    private $runTime;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $alternativeTitle;

    /**
     * @var string
     * @ORM\Column(type="string", length=32, nullable=false)
     */
    private $language;

    /**
     * @var array|string[]
     * @ORM\Column(type="array")
     */
    private $tags;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $references;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $copyright;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $transcript;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $abstract;

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
     * @var Collection|Subject[]
     * @ORM\ManyToMany(targetEntity="Subject", inversedBy="episodes")
     */
    private $subjects;

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
        $this->tags = [];
    }

}
