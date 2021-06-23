<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Entity;

use App\Repository\EpisodeRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\MediaBundle\Entity\Audio;
use Nines\MediaBundle\Entity\AudioContainerInterface;
use Nines\MediaBundle\Entity\AudioContainerTrait;
use Nines\MediaBundle\Entity\ImageContainerInterface;
use Nines\MediaBundle\Entity\ImageContainerTrait;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass=EpisodeRepository::class)
 */
class Episode extends AbstractEntity implements ImageContainerInterface, AudioContainerInterface {
    use ImageContainerTrait {
        ImageContainerTrait::__construct as protected image_constructor;
    }
    use AudioContainerTrait {
        AudioContainerTrait::__construct as protected audio_constructor;
    }

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $preserved;

    /**
     * @var DateTime
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * Run time in seconds.
     *
     * @var string
     * @ORM\Column(type="string", length=9, nullable=false)
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
     * @var Collection|Language[]
     * @ORM\ManyToMany(targetEntity="App\Entity\Language", inversedBy="episodes", cascade={"remove"})
     */
    private $languages;

    /**
     * @var array|string[]
     * @ORM\Column(type="array")
     */
    private $tags;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $bibliography;

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
     * @var array
     * @ORM\Column(type="json")
     */
    private $subjects;

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
     * @var Collection|Contribution[]
     * @ORM\OneToMany(targetEntity="Contribution", mappedBy="episode", cascade={"remove"})
     */
    private $contributions;

    public function __construct() {
        parent::__construct();
        $this->image_constructor();
        $this->audio_constructor();

        $this->preserved = false;
        $this->tags = [];
        $this->languages = new ArrayCollection();
        $this->subjects = [];
        $this->contributions = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString() : string {
        return $this->title;
    }

    public function getNumber() : ?int {
        return $this->number;
    }

    public function setNumber(int $number) : self {
        $this->number = $number;

        return $this;
    }

    public function getSlug() : string {
        if ($this->season) {
            return sprintf('S%02dE%02d', $this->season->getNumber(), $this->number);
        }

        return sprintf('E%02d', $this->number);
    }

    public function getDate() : ?DateTimeInterface {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date) : self {
        $this->date = $date;

        return $this;
    }

    public function getRunTime() : ?string {
        return $this->runTime;
    }

    public function setRunTime(string $runTime) : self {
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

    public function getTags() : ?array {
        return $this->tags;
    }

    public function setTags(array $tags) : self {
        $this->tags = $tags;

        return $this;
    }

    public function getBibliography() : ?string {
        return $this->bibliography;
    }

    public function setBibliography(string $bibliography) : self {
        $this->bibliography = $bibliography;

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

    public function getSubjects() : array {
        return $this->subjects;
    }

    public function addSubject($subject) : self {
        if ( ! in_array($subject, $this->subjects, true)) {
            $this->subjects[] = $subject;
        }

        return $this;
    }

    public function removeSubject($subject) : self {
        if (false !== ($key = array_search($subject, $this->subjects, true))) {
            array_splice($this->subjects, $key, 1);
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

    public function getAudio(string $mime) : ?Audio {
        foreach($this->audios as $audio) {
            if($audio->getMimeType() === $mime) {
                return $audio;
            }
        }
        return null;
    }

    public function getPreserved() : ?bool {
        return $this->preserved;
    }

    public function setPreserved(bool $preserved) : self {
        $this->preserved = $preserved;

        return $this;
    }

    /**
     * Sets the updated timestamp.
     *
     * @ORM\PreUpdate
     */
    public function preUpdate() : void {
        parent::preUpdate();
        $this->preserved = false;
    }

    /**
     * @return Collection|Language[]
     */
    public function getLanguages() : Collection {
        return $this->languages;
    }

    public function addLanguage(Language $language) : self {
        if ( ! $this->languages->contains($language)) {
            $this->languages[] = $language;
        }

        return $this;
    }

    public function removeLanguage(Language $language) : self {
        if ($this->languages->contains($language)) {
            $this->languages->removeElement($language);
        }

        return $this;
    }
}
