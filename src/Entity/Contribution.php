<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ContributionRepository;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass=ContributionRepository::class)
 */
class Contribution extends AbstractEntity {

    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="contributions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $person;

    /**
     * @var ContributorRole
     * @ORM\ManyToOne(targetEntity="ContributorRole", inversedBy="contributions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $contributorRole;

    /**
     * @var Podcast
     * @ORM\ManyToOne(targetEntity="Podcast", inversedBy="contributions")
     */
    private $podcast;

    /**
     * @var Season
     * @ORM\ManyToOne(targetEntity="Season", inversedBy="contributions")
     */
    private $season;

    /**
     * @var Episode
     * @ORM\ManyToOne(targetEntity="Episode", inversedBy="contributions")
     */
    private $episode;

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
