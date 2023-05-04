<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PodcastRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Nines\MediaBundle\Entity\ImageContainerInterface;
use Nines\MediaBundle\Entity\ImageContainerTrait;
use Nines\UtilBundle\Entity\AbstractEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PodcastRepository::class)]
#[ORM\Index(name: 'podcast_ft', columns: ['title', 'sub_title', 'description'], flags: ['fulltext'])]
class Podcast extends AbstractEntity implements ImageContainerInterface {
    use ImageContainerTrait {
        ImageContainerTrait::__construct as protected image_constructor;
    }

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $guid = null;

    #[ORM\Column(type: 'string')]
    private ?string $title = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $subTitle = null;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private ?bool $explicit = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Language')]
    private ?Language $language = null;

    #[ORM\Column(type: 'text', nullable: false)]
    private ?string $copyright = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $license = null;

    #[ORM\Column(type: 'text')]
    #[Assert\Url(normalizer: 'trim', protocols: ['http', 'https'])]
    private ?string $website = null;

    #[ORM\Column(type: 'string')]
    #[Assert\Url(normalizer: 'trim', protocols: ['http', 'https'])]
    private ?string $rss = null;

    #[ORM\ManyToOne(targetEntity: 'Publisher', inversedBy: 'podcasts')]
    #[ORM\OrderBy(['name' => 'ASC', 'id' => 'ASC'])]
    private ?Publisher $publisher = null;

    /**
     * @var Collection<int,Contribution>
     */
    #[ORM\OneToMany(targetEntity: 'Contribution', mappedBy: 'podcast')]
    #[ORM\OrderBy(['person' => 'ASC', 'contributorRole' => 'ASC'])]
    private $contributions;

    /**
     * @var Collection<int,Season>
     */
    #[ORM\OneToMany(targetEntity: 'Season', mappedBy: 'podcast')]
    #[ORM\OrderBy(['number' => 'ASC', 'title' => 'ASC'])]
    private $seasons;

    /**
     * @var Collection<int,Episode>
     */
    #[ORM\OneToMany(targetEntity: 'Episode', mappedBy: 'podcast')]
    #[ORM\OrderBy(['date' => 'ASC', 'episodeType' => 'DESC', 'number' => 'ASC', 'title' => 'ASC'])]
    private $episodes;

    /**
     * @var Collection<int,Category>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Category', inversedBy: 'podcasts')]
    #[ORM\OrderBy(['label' => 'ASC', 'id' => 'ASC'])]
    private $categories;

    /**
     * @var Collection<int,Export>
     */
    #[ORM\OneToMany(targetEntity: 'Export', mappedBy: 'podcast', orphanRemoval: true)]
    #[ORM\OrderBy(['created' => 'DESC', 'id' => 'DESC'])]
    private $exports;

    /**
     * @var Collection<int,Import>
     */
    #[ORM\OneToMany(targetEntity: 'Import', mappedBy: 'podcast', orphanRemoval: true)]
    #[ORM\OrderBy(['created' => 'DESC', 'id' => 'DESC'])]
    private $imports;

    public function __construct() {
        parent::__construct();
        $this->image_constructor();
        $this->contributions = new ArrayCollection();
        $this->seasons = new ArrayCollection();
        $this->episodes = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->exports = new ArrayCollection();
        $this->imports = new ArrayCollection();
    }

    public function __toString() : string {
        return $this->title;
    }

    public function getGuid() : ?string {
        return $this->guid;
    }

    public function setGuid(?string $guid) : self {
        $this->guid = $guid;

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

    public function getContributionsGroupedByPerson() : array {
        $contributions = [];

        foreach ($this->contributions as $contribution) {
            $person = $contribution->getPerson();
            if ( ! array_key_exists($person->getId(), $contributions)) {
                $contributions[$person->getId()] = [];
            }
            $contributions[$person->getId()][] = $contribution;
        }

        return $contributions;
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

    public function getOrphanedEpisodes() : array {
        $episodes = [];
        foreach ($this->getEpisodes() as $episode) {
            if (null === $episode->getSeason()) {
                $episodes[] = $episode;
            }
        }

        return $episodes;
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

    public function getLanguage() : ?Language {
        return $this->language;
    }

    public function setLanguage(?Language $language) : self {
        $this->language = $language;

        return $this;
    }

    public function getLicense() : ?string {
        return $this->license;
    }

    public function setLicense(?string $license) : self {
        $this->license = $license;

        return $this;
    }

    /**
     * @return Collection<int, Export>
     */
    public function getExports() : Collection {
        return $this->exports;
    }

    public function addExport(Export $export) : self {
        if ( ! $this->exports->contains($export)) {
            $this->exports[] = $export;
            $export->setPodcast($this);
        }

        return $this;
    }

    public function removeExport(Export $export) : self {
        if ($this->exports->removeElement($export)) {
            // set the owning side to null (unless already changed)
            if ($export->getPodcast() === $this) {
                $export->setPodcast(null);
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
        return ! $this->getActiveExports()->isEmpty();
    }

    /**
     * @return Collection<int, Import>
     */
    public function getImports() : Collection {
        return $this->imports;
    }

    public function addImport(Import $import) : self {
        if ( ! $this->imports->contains($import)) {
            $this->imports[] = $import;
            $import->setPodcast($this);
        }

        return $this;
    }

    public function removeImport(Import $import) : self {
        if ($this->imports->removeElement($import)) {
            // set the owning side to null (unless already changed)
            if ($import->getPodcast() === $this) {
                $import->setPodcast(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Import>
     */
    public function getActiveImports() : Collection {
        $expressionBuilder = Criteria::expr();
        $expression = $expressionBuilder->in('status', Import::getActiveStatuses());

        return $this->imports->matching(new Criteria($expression));
    }

    public function hasActiveImport() : ?bool {
        return ! $this->getActiveImports()->isEmpty();
    }

    public function getStatus() : array {
        $errors = [];
        $warnings = [];

        // if (empty(trim(strip_tags($this->getGuid() ?? '')))) {
        //     $warnings['Guid'] = 'No global unique identifier';
        // }
        if (empty(trim(strip_tags($this->getTitle() ?? '')))) {
            $errors['Title'] = 'No title';
        }
        if (empty(trim(strip_tags($this->getSubTitle() ?? '')))) {
            $warnings['Subtitle'] = 'No subtitle';
        }
        if (empty(trim(strip_tags($this->getWebsite() ?? '')))) {
            $errors['Website'] = 'No website';
        }
        if (empty(trim(strip_tags($this->getRss() ?? '')))) {
            $errors['Rss'] = 'No rss';
        }
        if (null === $this->getExplicit()) {
            $errors['Explicit'] = 'No explicit status';
        }
        if (empty(trim(strip_tags($this->getDescription() ?? '')))) {
            $errors['Description'] = 'No description';
        }
        if (empty(trim(strip_tags($this->getCopyright() ?? '')))) {
            $errors['Copyright'] = 'No copyright';
        }
        if (empty(trim(strip_tags($this->getLicense() ?? '')))) {
            $errors['License'] = 'No license';
        }
        if (null === $this->getPublisher()) {
            $errors['Publisher'] = 'No publisher';
        }
        if (null === $this->getLanguage()) {
            $errors['Language'] = 'No language';
        }
        if (null === $this->getContributions() || 0 === count($this->getContributions())) {
            $errors['Contributions'] = 'No contributions';
        }

        if (0 === count($this->getImages())) {
            $errors['Images'] = 'No images';
        }
        foreach ($this->getImages() as $image) {
            $imageWarnings = [];
            if (empty(trim(strip_tags($image->getDescription() ?? '')))) {
                $imageWarnings['Description'] = 'No description';
            }
            if (empty(trim(strip_tags($image->getLicense() ?? '')))) {
                $imageWarnings['License'] = 'No license';
            }
            if (count($imageWarnings) > 0) {
                $warnings["Image {$image->getOriginalName()}"] = $imageWarnings;
            }
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }
}
