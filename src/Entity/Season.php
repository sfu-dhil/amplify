<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SeasonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\MediaBundle\Entity\ImageContainerInterface;
use Nines\MediaBundle\Entity\ImageContainerTrait;
use Nines\UtilBundle\Entity\AbstractEntity;

#[ORM\Entity(repositoryClass: SeasonRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Season extends AbstractEntity implements ImageContainerInterface {
    use ImageContainerTrait {
        ImageContainerTrait::__construct as protected image_constructor;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $number = null;

    #[ORM\Column(type: 'string')]
    private ?string $title = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $subTitle = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: 'Podcast', inversedBy: 'seasons')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Podcast $podcast = null;

    #[ORM\ManyToOne(targetEntity: 'Publisher', inversedBy: 'seasons')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Publisher $publisher = null;

    #[ORM\Column(type: 'json', options: ['default' => '[]'])]
    private array $status = [];

    /**
     * @var Collection<int,Contribution>
     */
    #[ORM\OneToMany(targetEntity: 'Contribution', mappedBy: 'season')]
    private $contributions;

    /**
     * @var Collection<int,Episode>
     */
    #[ORM\OneToMany(targetEntity: 'Episode', mappedBy: 'season')]
    #[ORM\OrderBy(['date' => 'ASC', 'episodeType' => 'DESC', 'number' => 'ASC', 'title' => 'ASC'])]
    private $episodes;

    public function __construct() {
        parent::__construct();
        $this->image_constructor();
        $this->contributions = new ArrayCollection();
        $this->episodes = new ArrayCollection();
    }

    public function __toString() : string {
        return $this->title;
    }

    public function getSlug() : string {
        return sprintf('S%02d', $this->number);
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

    private function updateStatus() : void {
        $this->status = [];

        if (null === $this->getPodcast()) {
            $this->status[] = [
                'anchor' => 'season_podcast_label',
                'label' => 'Missing podcast',
            ];
        }
        if (empty(trim(strip_tags($this->getTitle() ?? '')))) {
            $this->status[] = [
                'anchor' => 'season_title_label',
                'label' => 'Missing title',
            ];
        }
        if (empty(trim(strip_tags($this->getDescription() ?? '')))) {
            $this->status[] = [
                'anchor' => 'season_description_label',
                'label' => 'Missing description',
            ];
        }
        foreach ($this->getImages() as $index => $image) {
            if (empty(trim(strip_tags($image->getDescription() ?? '')))) {
                $this->status[] = [
                    'anchor' => "season_images_{$index}_description_label",
                    'label' => 'Missing image description',
                ];
            }
        }
    }

    public function getStatus() : array {
        $status = $this->status;
        foreach ($status as &$item) {
            $item['route'] = 'season_edit';
            $item['route_params'] = [
                'podcast_id' => $this->getPodcast()->getId(),
                'id' => $this->getId(),
                '_fragment' => $item['anchor'],
            ];
        }

        // track episodes status dynamically
        if (0 === count($this->getEpisodes())) {
            $status[] = [
                'route' => 'episode_new',
                'route_params' => [
                    'podcast_id' => $this->getPodcast()->getId(),
                ],
                'label' => 'Missing episodes',
            ];
        }
        foreach ($this->getEpisodes() as $episode) {
            foreach ($episode->getStatus() as $item) {
                if (! array_key_exists('child', $item)) {
                    $item['label'] = "{$episode->getSlug()}: {$item['label']}";
                    $item['child'] = true;
                    $status[] = $item;
                }
            }
        }
        return $status;
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
