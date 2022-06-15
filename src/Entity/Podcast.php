<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Entity;

use App\Repository\PodcastRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\MediaBundle\Entity\ImageContainerInterface;
use Nines\MediaBundle\Entity\ImageContainerTrait;
use Nines\UtilBundle\Entity\AbstractEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PodcastRepository::class)
 */
class Podcast extends AbstractEntity implements ImageContainerInterface {
    use ImageContainerTrait {
        ImageContainerTrait::__construct as protected trait_constructor;
    }

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
     * @ORM\Column(type="text")
     * @Assert\Url(
     *     normalizer="trim",
     *     protocols={"http", "https"}
     * )
     */
    private $website;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Assert\Url(
     *     normalizer="trim",
     *     protocols={"http", "https"}
     * )
     */
    private $rss;

    /**
     * @var Publisher
     * @ORM\ManyToOne(targetEntity="Publisher", inversedBy="podcasts")
     */
    private $publisher;

    /**
     * @var Collection<int,Contribution>
     * @ORM\OneToMany(targetEntity="Contribution", mappedBy="podcast")
     */
    private $contributions;

    /**
     * @var Collection<int,Season>
     * @ORM\OneToMany(targetEntity="Season", mappedBy="podcast")
     */
    private $seasons;

    /**
     * @var Collection<int,Episode>
     * @ORM\OneToMany(targetEntity="Episode", mappedBy="podcast")
     */
    private $episodes;

    /**
     * @var Collection<int,Category>
     * @ORM\ManyToMany(targetEntity="App\Entity\Category", inversedBy="podcasts")
     */
    private $categories;

    public function __construct() {
        parent::__construct();
        $this->trait_constructor();
        $this->contributions = new ArrayCollection();
        $this->seasons = new ArrayCollection();
        $this->episodes = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString() : string {
        return $this->title;
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

    public function getExplicit() : ?bool {
        return $this->explicit;
    }

    public function setExplicit(bool $explicit) : self {
        $this->explicit = $explicit;

        return $this;
    }

    public function getDescription() : ?string {
        return $this->description;
    }

    public function setDescription(string $description) : self {
        $this->description = $description;

        return $this;
    }

    public function getCopyright() : ?string {
        return $this->copyright;
    }

    public function setCopyright(string $copyright) : self {
        $this->copyright = $copyright;

        return $this;
    }

    public function getWebsite() : ?string {
        return $this->website;
    }

    public function setWebsite(string $website) : self {
        $this->website = $website;

        return $this;
    }

    public function getRss() : ?string {
        return $this->rss;
    }

    public function setRss(string $rss) : self {
        $this->rss = $rss;

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
            $contribution->setPodcast($this);
        }

        return $this;
    }

    public function removeContribution(Contribution $contribution) : self {
        if ($this->contributions->contains($contribution)) {
            $this->contributions->removeElement($contribution);
            // set the owning side to null (unless already changed)
            if ($contribution->getPodcast() === $this) {
                $contribution->setPodcast(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int,Season>
     */
    public function getSeasons() : Collection {
        return $this->seasons;
    }

    public function addSeason(Season $season) : self {
        if ( ! $this->seasons->contains($season)) {
            $this->seasons[] = $season;
            $season->setPodcast($this);
        }

        return $this;
    }

    public function removeSeason(Season $season) : self {
        if ($this->seasons->contains($season)) {
            $this->seasons->removeElement($season);
            // set the owning side to null (unless already changed)
            if ($season->getPodcast() === $this) {
                $season->setPodcast(null);
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
            $episode->setPodcast($this);
        }

        return $this;
    }

    public function removeEpisode(Episode $episode) : self {
        if ($this->episodes->contains($episode)) {
            $this->episodes->removeElement($episode);
            // set the owning side to null (unless already changed)
            if ($episode->getPodcast() === $this) {
                $episode->setPodcast(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int,Category>
     */
    public function getCategories() : Collection {
        return $this->categories;
    }

    public function addCategory(Category $category) : self {
        if ( ! $this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(Category $category) : self {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
        }

        return $this;
    }
}
