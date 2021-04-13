<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Entity;

use App\Repository\LanguageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractTerm;

/**
 * @ORM\Entity(repositoryClass=LanguageRepository::class)
 */
class Language extends AbstractTerm
{
    /**
     * @var Collection|Episode[]
     * @ORM\ManyToMany(targetEntity="App\Entity\Episode", mappedBy="languages")
     */
    private $episodes;

    public function __construct() {
        parent::__construct();
        $this->episodes = new ArrayCollection();
    }

    /**
     * @return Collection|Episode[]
     */
    public function getEpisodes() : Collection {
        return $this->episodes;
    }

    public function addEpisode(Episode $episode) : self {
        if ( ! $this->episodes->contains($episode)) {
            $this->episodes[] = $episode;
            $episode->addLanguage($this);
        }

        return $this;
    }

    public function removeEpisode(Episode $episode) : self {
        if ($this->episodes->contains($episode)) {
            $this->episodes->removeElement($episode);
            $episode->removeLanguage($this);
        }

        return $this;
    }
}
