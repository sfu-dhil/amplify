<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LanguageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractTerm;

#[ORM\Entity(repositoryClass: LanguageRepository::class)]
class Language extends AbstractTerm {
    /**
     * @var Collection<int,Episode>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Episode', mappedBy: 'language')]
    private $episodes;

    public function __construct() {
        parent::__construct();
        $this->episodes = new ArrayCollection();
    }

    /**
     * @return Collection<int, Episode>
     */
    public function getEpisodes() : Collection {
        return $this->episodes;
    }

    public function addEpisode(Episode $episode) : self {
        if ( ! $this->episodes->contains($episode)) {
            $this->episodes[] = $episode;
            $episode->setLanguage($this);
        }

        return $this;
    }

    public function removeEpisode(Episode $episode) : self {
        if ($this->episodes->removeElement($episode)) {
            // set the owning side to null (unless already changed)
            if ($episode->getLanguage() === $this) {
                $episode->setLanguage(null);
            }
        }

        return $this;
    }
}
