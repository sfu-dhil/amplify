<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

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
     * @var string
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $alternativeTitle;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $explicit;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $copyright;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $category;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $website;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $rss;

    /**
     * @var array|string[]
     * @ORM\Column(type="array")
     */
    private $tags;

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

    public function __construct() {
        parent::__construct();
        $this->tags = [];
    }

    /**
     * {@inheritdoc}
     */
    public function __toString() : string {
        // TODO: Implement __toString() method.
    }
}
