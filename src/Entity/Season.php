<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Entity;

use App\Repository\SeasonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Nines\MediaBundle\Entity\ImageContainerInterface;
use Nines\MediaBundle\Entity\ImageContainerTrait;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass=SeasonRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Season extends AbstractEntity implements ImageContainerInterface {
    use ImageContainerTrait {
        ImageContainerTrait::__construct as protected trait_constructor;

    }

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $number;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $preserved;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subTitle;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $description;

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
     * @var Collection<int,Contribution>
     * @ORM\OneToMany(targetEntity="Contribution", mappedBy="season")
     */
    private $contributions;

    /**
     * @var Collection<int,Episode>
     * @ORM\OneToMany(targetEntity="Episode", mappedBy="season")
     * @ORM\OrderBy({"date": "ASC", "number": "ASC", "title": "ASC"})
     */
    private $episodes;

    /**
     * @var Collection<int,Export>
     * @ORM\OneToMany(targetEntity="Export", mappedBy="season", orphanRemoval=true)
     * @ORM\OrderBy({"created": "DESC", "id": "DESC"})
     */
    private $exports;

    public function __construct() {
        parent::__construct();
        $this->trait_constructor();
        $this->preserved = false;
        $this->contributions = new ArrayCollection();
        $this->episodes = new ArrayCollection();
        $this->exports = new ArrayCollection();
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

    public function setNumber(?int $number) : self {
        $this->number = $number;

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

    public function getFirstEpisode() : ?Episode {
        return $this->episodes->first();
    }

    public function getLastEpisode() : ?Episode {
        return $this->episodes->last();
    }

    public function getDescription() : ?string {
        return $this->description;
    }

    public function setDescription(string $description) : self {
        $this->description = $description;

        return $this;
    }

    public function getPodcast() : ?Podcast {
        return $this->podcast;
    }

    public function setPodcast(?Podcast $podcast) : self {
        $this->podcast = $podcast;

        return $this;
    }

    public function getPublisher() : ?Publisher {
        return $this->publisher;
    }

    public function setPublisher(?Publisher $publisher) : self {
        $this->publisher = $publisher;

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
            $contribution->setSeason($this);
        }

        return $this;
    }

    public function removeContribution(Contribution $contribution) : self {
        if ($this->contributions->contains($contribution)) {
            $this->contributions->removeElement($contribution);
            // set the owning side to null (unless already changed)
            if ($contribution->getSeason() === $this) {
                $contribution->setSeason(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int,Episode>
     */
    public function getEpisodes() : Collection {
        return $this->episodes;
    }

    public function addEpisode(Episode $episode) : self {
        if ( ! $this->episodes->contains($episode)) {
            $this->episodes[] = $episode;
            $episode->setSeason($this);
        }

        return $this;
    }

    public function removeEpisode(Episode $episode) : self {
        if ($this->episodes->contains($episode)) {
            $this->episodes->removeElement($episode);
            // set the owning side to null (unless already changed)
            if ($episode->getSeason() === $this) {
                $episode->setSeason(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Export>
     */
    public function getExports(): Collection {
        return $this->exports;
    }

    public function addExport(Export $export): self {
        if (!$this->exports->contains($export)) {
            $this->exports[] = $export;
            $export->setSeason($this);
        }

        return $this;
    }

    public function removeExport(Export $export): self {
        if ($this->exports->removeElement($export)) {
            // set the owning side to null (unless already changed)
            if ($export->getSeason() === $this) {
                $export->setSeason(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Export>
     */
    public function getActiveExports() : Collection {
        $expressionBuilder = Criteria::expr();
        $expression = $expressionBuilder->in('status', Export::getActiveStatuses());
        return $this->exports->matching(new Criteria($expression));
    }

    public function hasActiveExport() : ?bool {
        return !$this->getActiveExports()->isEmpty();
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
}
