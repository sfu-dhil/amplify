<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Repository;

use App\Entity\Episode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Episode find($id, $lockMode = null, $lockVersion = null)
 * @method Episode[] findAll()
 * @method Episode[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Episode findOneBy(array $criteria, array $orderBy = null)
 * @phpstan-extends ServiceEntityRepository<Episode>
 */
class EpisodeRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Episode::class);
    }

    public function indexQuery() : Query {
        return $this->createQueryBuilder('episode')
            ->orderBy('episode.id')
            ->getQuery()
        ;
    }

    public function typeaheadQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('episode');
        $qb->andWhere('episode.title LIKE :q');
        $qb->orderBy('episode.title', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery();
    }

    public function searchQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('episode');
        $qb->andWhere('episode.title LIKE :q');
        $qb->orderBy('episode.title', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery();
    }
}
