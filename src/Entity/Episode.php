<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Entity;

use App\Repository\EpisodeRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @var int
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
     * @ORM\Column(name="biblography", type="text")
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
     * @var null|Season
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

    public function __construct() {
        parent::__construct();
        $this->tags = [];
        $this->subjects = new ArrayCollection();
        $this->contributions = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString() : string {
        // TODO: Implement __toString() method.
    }

    public function getNumber() : ?int {
        return $this->number;
    }

    public function setNumber(int $number) : self {
        $this->number = $number;

        return $this;
    }

    public function getDate() : ?\DateTimeInterface {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date) : self {
        $this->date = $date;

        return $this;
    }

    public function getRunTime() : ?int {
        return $this->runTime;
    }

    public function setRunTime(int $runTime) : self {
        $this->runTime = $runTime;

        return $this;
    }

    public function getTitle() : ?string {
        return $this->title;
    }

    public function setTitle(string $title) : self {
        $this->title = $title;

        return $this;
    }

    public function getAlternativeTitle() : ?string {
        return $this->alternativeTitle;
    }

    public function setAlternativeTitle(?string $alternativeTitle) : self {
        $this->alternativeTitle = $alternativeTitle;

        return $this;
    }

    public function getLanguage() : ?string {
        return $this->language;
    }

    public function setLanguage(string $language) : self {
        $this->language = $language;

        return $this;
    }

    public function getTags() : ?array {
        return $this->tags;
    }

    public function setTags(array $tags) : self {
        $this->tags = $tags;

        return $this;
    }

    public function getReferences() : ?string {
        return $this->references;
    }

    public function setReferences(string $references) : self {
        $this->references = $references;

        return $this;
    }

    public function getCopyright() : ?string {
        return $this->copyright;
    }

    public function setCopyright(string $copyright) : self {
        $this->copyright = $copyright;

        return $this;
    }

    public function getTranscript() : ?string {
        return $this->transcript;
    }

    public function setTranscript(string $transcript) : self {
        $this->transcript = $transcript;

        return $this;
    }

    public function getAbstract() : ?string {
        return $this->abstract;
    }

    public function setAbstract(string $abstract) : self {
        $this->abstract = $abstract;

        return $this;
    }

    public function getSeason() : ?Season {
        return $this->season;
    }

    public function setSeason(?Season $season) : self {
        $this->season = $season;

        return $this;
    }

    public function getPodcast() : ?Podcast {
        return $this->podcast;
    }

    public function setPodcast(?Podcast $podcast) : self {
        $this->podcast = $podcast;

        return $this;
    }

    /**
     * @return Collection|Subject[]
     */
    public function getSubjects() : Collection {
        return $this->subjects;
    }

    public function addSubject(Subject $subject) : self {
        if ( ! $this->subjects->contains($subject)) {
            $this->subjects[] = $subject;
        }

        return $this;
    }

    public function removeSubject(Subject $subject) : self {
        if ($this->subjects->contains($subject)) {
            $this->subjects->removeElement($subject);
        }

        return $this;
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
            $contribution->setEpisode($this);
        }

        return $this;
    }

    public function removeContribution(Contribution $contribution) : self {
        if ($this->contributions->contains($contribution)) {
            $this->contributions->removeElement($contribution);
            // set the owning side to null (unless already changed)
            if ($contribution->getEpisode() === $this) {
                $contribution->setEpisode(null);
            }
        }

        return $this;
    }
}
