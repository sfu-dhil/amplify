<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Repository;

use App\Entity\Podcast;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Podcast find($id, $lockMode = null, $lockVersion = null)
 * @method null|Podcast findOneBy(array $criteria, array $orderBy = null)
 * @method Podcast[]    findAll()
 * @method Podcast[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PodcastRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Podcast::class);
    }

    /**
     * @return Query
     */
    public function indexQuery() {
        return $this->createQueryBuilder('podcast')
            ->orderBy('podcast.id')
            ->getQuery()
        ;
    }

    /**
     * @param string $q
     *
     * @return Collection|Podcast[]
     */
    public function typeaheadQuery($q) {
        $qb = $this->createQueryBuilder('podcast');
        $qb->andWhere('podcast.title LIKE :q');
        $qb->orderBy('podcast.title', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery()->execute();
    }

    /**
     * @param string $q
     *
     * @return Query
     */
    public function searchQuery($q) {
        $qb = $this->createQueryBuilder('podcast');
        $qb->andWhere('podcast.title LIKE :q');
        $qb->orderBy('podcast.title', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery();
    }
}
