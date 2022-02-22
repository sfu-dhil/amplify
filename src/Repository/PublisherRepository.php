<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Repository;

use App\Entity\Publisher;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Publisher find($id, $lockMode = null, $lockVersion = null)
 * @method Publisher[] findAll()
 * @method Publisher[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Publisher findOneBy(array $criteria, array $orderBy = null)
 * @phpstan-extends ServiceEntityRepository<Publisher>
 */
class PublisherRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Publisher::class);
    }

    public function indexQuery() : Query {
        return $this->createQueryBuilder('publisher')
            ->orderBy('publisher.id')
            ->getQuery()
        ;
    }

    public function typeaheadQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('publisher');
        $qb->andWhere('publisher.name LIKE :q');
        $qb->orderBy('publisher.name', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery();
    }

    public function searchQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('publisher');
        $qb->andWhere('publisher.name LIKE :q');
        $qb->orderBy('publisher.name', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery();
    }
}
