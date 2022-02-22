<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Repository;

use App\Entity\Season;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Season find($id, $lockMode = null, $lockVersion = null)
 * @method Season[] findAll()
 * @method Season[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Season findOneBy(array $criteria, array $orderBy = null)
 * @phpstan-extends ServiceEntityRepository<Season>
 */
class SeasonRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Season::class);
    }

    public function indexQuery() : Query {
        return $this->createQueryBuilder('season')
            ->orderBy('season.id')
            ->getQuery()
        ;
    }

    public function typeaheadQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('season');
        $qb->andWhere('season.title LIKE :q');
        $qb->orderBy('season.title', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery();
    }

    public function searchQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('season');
        $qb->andWhere('season.title LIKE :q');
        $qb->orderBy('season.title', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery();
    }
}
