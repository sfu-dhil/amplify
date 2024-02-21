<?php

declare(strict_types=1);

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

#[ORM\Entity(repositoryClass: EpisodeRepository::class)]
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

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $guid = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false, options: ['default' => 'full'])]
    private ?string $episodeType = null;

    #[ORM\Column(type: 'float')]
    private ?float $number = null;

    #[ORM\Column(type: 'date')]
    private ?DateTimeInterface $date = null;

    /**
     * Run time in seconds.
     */
    #[ORM\Column(type: 'string', length: 9, nullable: false)]
    private ?string $runTime = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private ?string $title = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $subTitle = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $explicit = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bibliography = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $transcript = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $permissions = null;

    #[ORM\Column(type: 'json', options: ['default' => '[]'])]
    private array $keywords = [];

    #[ORM\Column(type: 'json', options: ['default' => '[]'])]
    private array $status = [];

    #[ORM\ManyToOne(targetEntity: 'Season', inversedBy: 'episodes')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Season $season = null;

    #[ORM\ManyToOne(targetEntity: 'Podcast', inversedBy: 'episodes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Podcast $podcast = null;

    /**
     * @var Collection<int,Contribution>
     */
    #[ORM\OneToMany(targetEntity: 'Contribution', mappedBy: 'episode', cascade: ['remove'])]
    private $contributions;

    public function __construct() {
        parent::__construct();
        $this->image_constructor();
        $this->audio_constructor();
        $this->pdf_constructor();
        $this->contributions = new ArrayCollection();
    }

    public function __toString() : string {
        return $this->title;
    }

    public function updateStatus() : void {
        $status = [];

        if (null === $this->getPodcast()) {
            $status[] = [
                'anchor' => 'episode_podcast_label',
                'label' => 'Missing podcast',
            ];
        }
        if (null === $this->getSeason()) {
            $status[] = [
                'anchor' => 'episode_season_label',
                'label' => 'Missing season',
            ];
        }
        if (empty(trim(strip_tags($this->getEpisodeType() ?? '')))) {
            $status[] = [
                'anchor' => 'episode_episodeType_label',
                'label' => 'Missing episode type',
            ];
        }
        if (null === $this->getNumber()) {
            $status[] = [
                'anchor' => 'episode_number_label',
                'label' => 'Missing episode number',
            ];
        }
        if (null === $this->getDate()) {
            $status[] = [
                'anchor' => 'episode_date_label',
                'label' => 'Missing date',
            ];
        }
        if (null === $this->getRunTime()) {
            $status[] = [
                'anchor' => 'episode_runTime_label',
                'label' => 'Missing run time',
            ];
        }
        if (empty(trim(strip_tags($this->getTitle() ?? '')))) {
            $status[] = [
                'anchor' => 'episode_title_label',
                'label' => 'Missing title',
            ];
        }
        if (empty(trim(strip_tags($this->getDescription() ?? '')))) {
            $status[] = [
                'anchor' => 'episode_description_label',
                'label' => 'Missing description',
            ];
        }

        if (0 === count($this->getAudios())) {
            $status[] = [
                'anchor' => 'episode_audios_label',
                'label' => 'Missing audio',
            ];
        }
        foreach ($this->getImages() as $index => $image) {
            if (empty(trim(strip_tags($image->getDescription() ?? '')))) {
                $status[] = [
                    'anchor' => "episode_images_{$index}_description_label",
                    'label' => 'Missing image description',
                ];
            }
        }
        if (0 === count($this->getPdfs()) && empty(trim(strip_tags($this->getTranscript() ?? '')))) {
            $status[] = [
                'anchor' => 'episode_transcript_label',
                'label' => 'Missing transcript',
            ];
        }

        $this->status = $status;
    }

    public function getGuid() : ?string {
        return $this->guid;
    }

    public function setGuid(?string $guid) : self {
        $this->guid = $guid;

        return $this;
    }

    public function getEpisodeType() : ?string {
        return $this->episodeType;
    }

    public function setEpisodeType(string $episodeType) : self {
        $this->episodeType = $episodeType;

        return $this;
    }

    public function getNumber() : ?float {
        return $this->number;
    }

    public function setNumber(float $number) : self {
        $this->number = $number;

        return $this;
    }

    public function getSlug() : string {
        $seasonSlug = $this->season?->getId() ? $this->season->getSlug() : '';
        $episodeNumber = (float) $this->number; // removes trailing zeros if not needed
        if ('bonus' === $this->getEpisodeType()) {
            return "{$seasonSlug}B{$episodeNumber}";
        }
        if ('trailer' === $this->getEpisodeType()) {
            return "{$seasonSlug}T{$episodeNumber}";
        }

        return "{$seasonSlug}E{$episodeNumber}";
    }

    public function getLongSlug() : string {
        $seasonSlug = $this->season?->getId() ? $this->season->getLongSlug() : '';
        $episodeNumber = (float) $this->number; // removes trailing zeros if not needed
        if ('bonus' === $this->getEpisodeType()) {
            return "{$seasonSlug} Bonus Episode {$episodeNumber}";
        }
        if ('trailer' === $this->getEpisodeType()) {
            return "{$seasonSlug} Tailer {$episodeNumber}";
        }

        return "{$seasonSlug} Episode {$episodeNumber}";
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

    public function getExplicit() : ?bool {
        return $this->explicit;
    }

    public function setExplicit(bool $explicit) : self {
        $this->explicit = $explicit;

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

    public function getStatus() : array {
        $status = $this->status;
        foreach ($status as &$existingStatus) {
            $existingStatus['route'] = 'episode_edit';
            $existingStatus['route_params'] = [
                'podcast_id' => $this->getPodcast()->getId(),
                'id' => $this->getId(),
                '_fragment' => $existingStatus['anchor'],
            ];
        }

        return $status;
    }

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

    public function getPermissions() : ?string {
        return $this->permissions;
    }

    public function setPermissions(?string $permissions) : self {
        $this->permissions = $permissions;

        return $this;
    }

    #[ORM\PrePersist]
    public function prePersist() : void {
        parent::prePersist();
        $this->updateStatus();
    }

    #[ORM\PreUpdate]
    public function preUpdate() : void {
        parent::preUpdate();
        $this->updateStatus();
    }
}
