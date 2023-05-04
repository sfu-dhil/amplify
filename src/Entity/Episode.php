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

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Language', inversedBy: 'episodes')]
    private ?Language $language = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bibliography = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $transcript = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $permissions = null;

    #[ORM\Column(type: 'json')]
    private array $subjects = [];

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

    public function setSubjects(array $subjects) : self {
        $this->subjects = $subjects;

        return $this;
    }

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

    public function getLanguage() : ?Language {
        return $this->language;
    }

    public function setLanguage(?Language $language) : self {
        $this->language = $language;

        return $this;
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
        //     $warnings['Guid'] = 'No global unique identifier';
        // }
        if (null === $this->getPodcast()) {
            $errors['Podcast'] = 'No podcast';
        }
        if (null === $this->getSeason()) {
            $errors['Season'] = 'No season';
        }
        if (empty(trim(strip_tags($this->getEpisodeType() ?? '')))) {
            $errors['Episode type'] = 'No episode type';
        }
        if (null === $this->getNumber()) {
            $errors['Episode number'] = 'No episode number';
        }
        if (empty(trim(strip_tags($this->getTitle() ?? '')))) {
            $errors['Title'] = 'No title';
        }
        if (empty(trim(strip_tags($this->getSubTitle() ?? '')))) {
            $warnings['Subtitle'] = 'No subtitle';
        }
        if (null === $this->getExplicit()) {
            $warnings['Explicit'] = 'No explicit status';
        }
        if (null === $this->getDate()) {
            $errors['Date'] = 'No date';
        }
        if (null === $this->getRunTime()) {
            $errors['Run time'] = 'No run time';
        }
        if (null === $this->getLanguage()) {
            $errors['Language'] = 'No primary language';
        }
        if (empty(trim(strip_tags($this->getDescription() ?? '')))) {
            $errors['Description'] = 'No description';
        }
        if (empty(trim(strip_tags($this->getBibliography() ?? '')))) {
            $errors['Bibliography'] = 'No bibliography';
        }
        if (empty(trim(strip_tags($this->getTranscript() ?? '')))) {
            $errors['Transcript'] = 'No transcript';
        }
        if (empty(trim(strip_tags($this->getPermissions() ?? '')))) {
            $errors['Permissions'] = 'No permissions';
        }
        if (null === $this->getSubjects() || 0 === count($this->getSubjects())) {
            $errors['Subjects'] = 'No subjects';
        }
        if (null === $this->getContributions() || 0 === count($this->getContributions())) {
            $errors['Contributions'] = 'No contributions';
        }

        if (0 === count($this->getAudios())) {
            $errors['Audios'] = 'No audio';
        }
        foreach ($this->getAudios() as $audio) {
            $audioWarnings = [];
            if (empty(trim(strip_tags($audio->getDescription() ?? '')))) {
                $audioWarnings['Description'] = 'No description';
            }
            if (empty(trim(strip_tags($audio->getLicense() ?? '')))) {
                $audioWarnings['License'] = 'No license';
            }
            if (count($audioWarnings) > 0) {
                $warnings["Audio {$audio->getOriginalName()}"] = $audioWarnings;
            }
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

        foreach ($this->getPdfs() as $pdf) {
            $pdfWarnings = [];
            if (empty(trim(strip_tags($pdf->getDescription() ?? '')))) {
                $pdfWarnings['Description'] = 'No description';
            }
            if (empty(trim(strip_tags($pdf->getLicense() ?? '')))) {
                $pdfWarnings['License'] = 'No license';
            }
            if (count($pdfWarnings) > 0) {
                $warnings["PDF {$pdf->getOriginalName()}"] = $pdfWarnings;
            }
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }
}
