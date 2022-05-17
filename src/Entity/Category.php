<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractTerm;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 */
class Category extends AbstractTerm {
    /**
     * @var Collection<int,Podcast>
     * @ORM\ManyToMany(targetEntity="Podcast", mappedBy="categories")
     */
    private $podcasts;

    public function __construct() {
        parent::__construct();
        $this->podcasts = new ArrayCollection();
    }

    /**
     * @return Collection<int,Podcast>
     */
    public function getPodcasts() : Collection {
        return $this->podcasts;
    }

    public function addPodcast(Podcast $podcast) : self {
        if ( ! $this->podcasts->contains($podcast)) {
            $this->podcasts[] = $podcast;
            $podcast->addCategory($this);
        }

        return $this;
    }

    public function removePodcast(Podcast $podcast) : self {
        if ($this->podcasts->contains($podcast)) {
            $this->podcasts->removeElement($podcast);
            $podcast->removeCategory($this);
        }

        return $this;
    }
}
