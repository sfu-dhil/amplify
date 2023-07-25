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
use Symfony\Component\Intl\Languages;
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

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Language]
    private ?string $languageCode = null;

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

    #[ORM\Column(type: 'json', options: ['default' => '[]'])]
    private array $categories = [];

    #[ORM\Column(type: 'json', options: ['default' => '[]'])]
    private array $keywords = [];

    /**
     * @var Collection<int,Share>
     */
    #[ORM\OneToMany(targetEntity: 'Share', mappedBy: 'podcast', orphanRemoval: true)]
    #[ORM\OrderBy(['created' => 'DESC', 'id' => 'DESC'])]
    private $shares;

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

    protected static $ITUNES_CATEGORIES = [
        'Arts',
        'Arts - Books',
        'Arts - Design',
        'Arts - Fashion & Beauty',
        'Arts - Food',
        'Arts - Performing Arts',
        'Arts - Visual Arts',
        'Business',
        'Business - Careers',
        'Business - Entrepreneurship',
        'Business - Investing',
        'Business - Management',
        'Business - Marketing',
        'Business - Non-Profit',
        'Comedy',
        'Comedy - Comedy Interviews',
        'Comedy - Improv',
        'Comedy - Stand-Up',
        'Education',
        'Education - Courses', // Not sure about this one
        'Education - How To',
        'Education - Language Learning',
        'Education - Self-Improvement',
        'Fiction',
        'Fiction - Comedy Fiction',
        'Fiction - Drama',
        'Fiction - Science Fiction',
        'Government',
        'History',
        'Health & Fitness',
        'Health & Fitness - Alternative Health',
        'Health & Fitness - Fitness',
        'Health & Fitness - Medicine',
        'Health & Fitness - Mental Health',
        'Health & Fitness - Nutrition',
        'Health & Fitness - Sexuality',
        'Kids & Family',
        'Kids & Family - Education for Kids',
        'Kids & Family - Parenting',
        'Kids & Family - Pets & Animals',
        'Kids & Family - Stories for Kids',
        'Leisure',
        'Leisure - Animation & Manga',
        'Leisure - Automotive',
        'Leisure - Aviation',
        'Leisure - Crafts',
        'Leisure - Games',
        'Leisure - Hobbies',
        'Leisure - Home & Garden',
        'Leisure - Video Games',
        'Music',
        'Music - Music Commentary',
        'Music - Music History',
        'Music - Music Interviews',
        'News',
        'News - Business News',
        'News - Daily News',
        'News - Entertainment News',
        'News - News Commentary',
        'News - Politics',
        'News - Sports News',
        'News - Tech News',
        'Religion & Spirituality',
        'Religion & Spirituality - Buddhism',
        'Religion & Spirituality - Christianity',
        'Religion & Spirituality - Hinduism',
        'Religion & Spirituality - Islam',
        'Religion & Spirituality - Judaism',
        'Religion & Spirituality - Religion',
        'Religion & Spirituality - Spirituality',
        'Science',
        'Science - Astronomy',
        'Science - Chemistry',
        'Science - Earth Sciences',
        'Science - Life Sciences',
        'Science - Mathematics',
        'Science - Natural Sciences',
        'Science - Nature',
        'Science - Physics',
        'Science - Social Sciences',
        'Society & Culture',
        'Society & Culture - Documentary',
        'Society & Culture - Personal Journals',
        'Society & Culture - Philosophy',
        'Society & Culture - Places & Travel',
        'Society & Culture - Relationships',
        'Sports',
        'Sports - Baseball',
        'Sports - Basketball',
        'Sports - Cricket',
        'Sports - Fantasy Sports',
        'Sports - Football',
        'Sports - Golf',
        'Sports - Hockey',
        'Sports - Rugby',
        'Sports - Running',
        'Sports - Soccer',
        'Sports - Swimming',
        'Sports - Tennis',
        'Sports - Volleyball',
        'Sports - Wilderness',
        'Sports - Wrestling',
        'Technology',
        'True Crime',
        'TV & Film',
        'TV & Film - After Shows',
        'TV & Film - Film History',
        'TV & Film - Film Interviews',
        'TV & Film - Film Reviews',
        'TV & Film - TV Reviews',
    ];

    public function __construct() {
        parent::__construct();
        $this->image_constructor();
        $this->shares = new ArrayCollection();
        $this->contributions = new ArrayCollection();
        $this->seasons = new ArrayCollection();
        $this->episodes = new ArrayCollection();
        $this->exports = new ArrayCollection();
        $this->imports = new ArrayCollection();
    }

    public function __toString() : string {
        return $this->title ?? '';
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
     * @return Collection<int,Share>
     */
    public function getShares() : Collection {
        return $this->shares;
    }

    public function addShare(Share $share) : self {
        if ( ! $this->shares->contains($share)) {
            $this->shares[] = $share;
            $share->setPodcast($this);
        }

        return $this;
    }

    public function removeShare(Share $share) : self {
        if ($this->shares->contains($share)) {
            $this->shares->removeElement($share);
            // set the owning side to null (unless already changed)
            if ($share->getPodcast() === $this) {
                $share->setPodcast(null);
            }
        }

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

    public function getAllItunesCategories() : array {
        return self::$ITUNES_CATEGORIES;
    }

    public function setCategories(array $categories) : self {
        $this->categories = $categories;

        return $this;
    }

    public function getCategories() : array {
        return $this->categories;
    }

    public function addCategory(string $category) : self {
        if ( ! in_array($category, $this->categories, true)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(string $category) : self {
        if (false !== ($key = array_search($category, $this->categories, true))) {
            array_splice($this->categories, $key, 1);
        }

        return $this;
    }

    public function setKeywords(array $keywords) : self {
        $this->keywords = $keywords;

        return $this;
    }

    public function getKeywords() : array {
        return $this->keywords;
    }

    public function addKeyword(string $keyword) : self {
        if ( ! in_array($keyword, $this->keywords, true)) {
            $this->keywords[] = $keyword;
        }

        return $this;
    }

    public function removeKeyword(string $keyword) : self {
        if (false !== ($key = array_search($keyword, $this->keywords, true))) {
            array_splice($this->keywords, $key, 1);
        }

        return $this;
    }

    public function getAlpha3LanguageCode() : ?string {
        if (null === $this->languageCode) {
            return null;
        }

        return Languages::getAlpha3Code(mb_substr($this->languageCode, 0, 2));
    }

    public function getLanguageCode() : ?string {
        return $this->languageCode;
    }

    public function setLanguageCode(?string $languageCode) : self {
        $this->languageCode = $languageCode;

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
        //     $warnings['podcast_guid_label'] = 'Missing global unique identifier';
        // }
        if (empty(trim(strip_tags($this->getTitle() ?? '')))) {
            $errors['podcast_title_label'] = 'Missing title';
        }
        // if (empty(trim(strip_tags($this->getSubTitle() ?? '')))) {
        //     $warnings['podcast_subTitle_label'] = 'Missing subtitle';
        // }
        // if (null === $this->getLanguageCode()) {
        //     $warnings['podcast_languageCode_label'] = 'Missing primary language';
        // }
        if (null === $this->getExplicit()) {
            $errors['podcast_explicit_label'] = 'Missing explicit status';
        }
        if (empty(trim(strip_tags($this->getDescription() ?? '')))) {
            $errors['podcast_description_label'] = 'Missing description';
        }
        if (empty(trim(strip_tags($this->getCopyright() ?? '')))) {
            $errors['podcast_copyright_label'] = 'Missing copyright';
        }
        // if (empty(trim(strip_tags($this->getLicense() ?? '')))) {
        //     $warnings['podcast_license_label'] = 'Missing license';
        // }
        if (empty(trim(strip_tags($this->getWebsite() ?? '')))) {
            $errors['podcast_website_label'] = 'Missing website';
        }
        if (empty(trim(strip_tags($this->getRss() ?? '')))) {
            $errors['podcast_rss_label'] = 'Missing rss';
        }
        if (null === $this->getCategories() || 0 === count($this->getCategories())) {
            $errors['podcast_categories_label'] = 'Missing Apple podcast categories';
        }
        // if (null === $this->getPublisher()) {
        //     $warnings['podcast_publisher_label'] = 'Missing publisher';
        // }
        // if (null === $this->getContributions() || 0 === count($this->getContributions())) {
        //     $warnings['podcast_contributions_label'] = 'Missing contributors';
        // }
        if (0 === count($this->getImages())) {
            $errors['podcast_images_label'] = 'Missing image';
        }
        foreach ($this->getImages() as $index => $image) {
            if (empty(trim(strip_tags($image->getDescription() ?? '')))) {
                $errors["podcast_images_{$index}_description_label"] = 'Missing image description';
            }
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }
}
