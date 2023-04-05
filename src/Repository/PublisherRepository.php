<?php

declare(strict_types=1);

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
 *
 * @phpstan-extends ServiceEntityRepository<Publisher>
 */
class PublisherRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Publisher::class);
    }

    public function indexQuery() : Query {
        return $this->createQueryBuilder('publisher')
            ->orderBy('publisher.name', 'ASC')
            ->getQuery()
        ;
    }

    public function typeaheadQuery(string $q) : Query {
        return $this->createQueryBuilder('publisher')
            ->andWhere('publisher.name LIKE :q')
            ->orderBy('publisher.name', 'ASC')
            ->setParameter('q', "%{$q}%")
            ->getQuery()
        ;
    }

    public function searchQuery(string $q) : Query {
        return $this->createQueryBuilder('publisher')
            ->addSelect('MATCH (publisher.name, publisher.description) AGAINST(:q BOOLEAN) as HIDDEN score')
            ->andHaving('score > 0')
            ->orderBy('score', 'DESC')
            ->addOrderBy('publisher.name', 'ASC')
            ->setParameter('q', $q)
            ->getQuery()
        ;
    }
}
