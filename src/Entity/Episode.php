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

    #[ORM\Column(type: 'integer')]
    private ?int $number = null;

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

    public function getNumber() : ?int {
        return $this->number;
    }

    public function setNumber(int $number) : self {
        $this->number = $number;

        return $this;
    }

    public function getSlug() : string {
        $seasonSlug = $this->season?->getId() ? $this->season->getSlug() : '';
        if ('bonus' === $this->getEpisodeType()) {
            return "{$seasonSlug} Bonus {$this->number}";
        }
        if ('trailer' === $this->getEpisodeType()) {
            return "{$seasonSlug} Trailer {$this->number}";
        }

        return $seasonSlug . sprintf('E%02d', $this->number);
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

    public function getStatus() : array {
        $errors = [];
        $warnings = [];

        // if (empty(trim(strip_tags($this->getGuid() ?? '')))) {
        //     $warnings['episode_guid_label'] = 'Missing global unique identifier';
        // }
        if (null === $this->getPodcast()) {
            $errors['episode_podcast_label'] = 'Missing podcast';
        }
        if (null === $this->getSeason()) {
            $errors['episode_season_label'] = 'Missing season';
        }
        if (empty(trim(strip_tags($this->getEpisodeType() ?? '')))) {
            $errors['episode_episodeType_label'] = 'Missing episode type';
        }
        if (null === $this->getNumber()) {
            $errors['episode_number_label'] = 'Missing episode number';
        }
        if (null === $this->getDate()) {
            $errors['episode_date_label'] = 'Missing date';
        }
        if (null === $this->getRunTime()) {
            $errors['episode_runTime_label'] = 'Missing run time';
        }
        if (empty(trim(strip_tags($this->getTitle() ?? '')))) {
            $errors['episode_title_label'] = 'Missing title';
        }
        // if (empty(trim(strip_tags($this->getSubTitle() ?? '')))) {
        //     $warnings['episode_subTitle_label'] = 'Missing subtitle';
        // }
        // if (null === $this->getExplicit()) {
        //     $warnings['episode_explicit_label'] = 'Missing explicit status';
        // }
        if (empty(trim(strip_tags($this->getDescription() ?? '')))) {
            $errors['episode_description_label'] = 'Missing description';
        }
        // if (empty(trim(strip_tags($this->getBibliography() ?? '')))) {
        //     $warnings['episode_bibliography_label'] = 'Missing bibliography';
        // }
        // if (empty(trim(strip_tags($this->getPermissions() ?? '')))) {
        //     $warnings['episode_permissions_label'] = 'Missing permissions';
        // }
        // if (null === $this->getContributions() || 0 === count($this->getContributions())) {
        //     $warnings['episode_contributions_label'] = 'Missing contributors';
        // }

        if (0 === count($this->getAudios())) {
            $errors['episode_audios_label'] = 'Missing audio';
        }
        foreach ($this->getImages() as $index => $image) {
            if (empty(trim(strip_tags($image->getDescription() ?? '')))) {
                $errors["episode_images_{$index}_description_label"] = 'Missing image description';
            }
        }
        if (0 === count($this->getPdfs()) && empty(trim(strip_tags($this->getTranscript() ?? '')))) {
            $errors['episode_transcript_label'] = 'Missing transcript';
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }
}
