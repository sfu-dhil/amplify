<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Entity;

use App\Repository\EpisodeRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\MediaBundle\Entity\Audio;
use Nines\MediaBundle\Entity\AudioContainerInterface;
use Nines\MediaBundle\Entity\AudioContainerTrait;
use Nines\MediaBundle\Entity\ImageContainerInterface;
use Nines\MediaBundle\Entity\ImageContainerTrait;
use Nines\MediaBundle\Entity\PdfContainerInterface;
use Nines\MediaBundle\Entity\PdfContainerTrait;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass=EpisodeRepository::class)
 */
class Episode extends AbstractEntity implements ImageContainerInterface, AudioContainerInterface, PdfContainerInterface {
    use ImageContainerTrait {
        ImageContainerTrait::__construct as protected image_constructor;

    }
    use AudioContainerTrait {
        AudioContainerTrait::__construct as protected audio_constructor;

    }
    use PdfContainerTrait {
        PdfContainerTrait::__construct as protected pdf_constructor;

    }

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $guid;

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
     * @var DateTimeInterface
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
    private $subTitle;

    /**
     * @var Language
     * @ORM\ManyToOne(targetEntity="App\Entity\Language", inversedBy="episodes")
     */
    private $language;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $bibliography;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $transcript;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $permissions;

    /**
     * @var string[]
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
     * @var Collection<int,Contribution>
     * @ORM\OneToMany(targetEntity="Contribution", mappedBy="episode", cascade={"remove"})
     */
    private $contributions;

    public function __construct() {
        parent::__construct();
        $this->image_constructor();
        $this->audio_constructor();
        $this->pdf_constructor();

        $this->preserved = false;
        $this->subjects = [];
        $this->contributions = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString() : string {
        return $this->title;
    }

    public function getGuid() : ?string {
        return $this->guid;
    }

    public function setGuid(string $guid) : self {
        $this->guid = $guid;

        return $this;
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

    public function getSubTitle() : ?string {
        return $this->subTitle;
    }

    public function setSubTitle(?string $subTitle) : self {
        $this->subTitle = $subTitle;

        return $this;
    }

    public function getBibliography() : ?string {
        return $this->bibliography;
    }

    public function setBibliography(?string $bibliography) : self {
        $this->bibliography = $bibliography;

        return $this;
    }

    public function getTranscript() : ?string {
        return $this->transcript;
    }

    public function setTranscript(string $transcript) : self {
        $this->transcript = $transcript;

        return $this;
    }

    public function getDescription() : ?string {
        return $this->description;
    }

    public function setDescription(string $description) : self {
        $this->description = $description;

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

    public function setSubjects(array $subjects) : self {
        $this->subjects = $subjects;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getSubjects() : array {
        return $this->subjects;
    }

    public function addSubject(string $subject) : self {
        if ( ! in_array($subject, $this->subjects, true)) {
            $this->subjects[] = $subject;
        }

        return $this;
    }

    public function removeSubject(string $subject) : self {
        if (false !== ($key = array_search($subject, $this->subjects, true))) {
            array_splice($this->subjects, $key, 1);
        }

        return $this;
    }

    /**
     * @return Collection<int,Contribution>
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
        foreach ($this->audios as $audio) {
            if ($audio->getMimeType() === $mime) {
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

    public function getLanguage() : ?Language {
        return $this->language;
    }

    public function setLanguage(?Language $language) : self {
        $this->language = $language;

        return $this;
    }

    public function getPermissions(): ?string
    {
        return $this->permissions;
    }

    public function setPermissions(?string $permissions): self
    {
        $this->permissions = $permissions;

        return $this;
    }
}
