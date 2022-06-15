<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Repository;

use App\Entity\Podcast;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Podcast find($id, $lockMode = null, $lockVersion = null)
 * @method Podcast[] findAll()
 * @method Podcast[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Podcast findOneBy(array $criteria, array $orderBy = null)
 * @phpstan-extends ServiceEntityRepository<Podcast>
 */
class PodcastRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Podcast::class);
    }

    public function indexQuery() : Query {
        return $this->createQueryBuilder('podcast')
            ->orderBy('podcast.id')
            ->getQuery()
        ;
    }

    public function typeaheadQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('podcast');
        $qb->andWhere('podcast.title LIKE :q');
        $qb->orderBy('podcast.title', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery();
    }

    public function searchQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('podcast');
        $qb->andWhere('podcast.title LIKE :q');
        $qb->orderBy('podcast.title', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery();
    }
}
