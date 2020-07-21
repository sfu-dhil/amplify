<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SubjectRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;
use Nines\UtilBundle\Entity\AbstractTerm;

/**
 * @ORM\Entity(repositoryClass=SubjectRepository::class)
 */
class Subject extends AbstractTerm {

    /**
     * @var Collection|Episode[]
     * @ORM\ManyToMany(targetEntity="Episode", mappedBy="subjects")
     */
    private $episodes;

    public function __construct() {
        parent::__construct();
    }

}
