<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Podcast;
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

    public function indexQuery(Podcast $podcast) : Query {
        return $this->createQueryBuilder('publisher')
            ->andWhere('publisher.podcast = :p')
            ->orderBy('publisher.name', 'ASC')
            ->setParameter('p', $podcast->getId())
            ->getQuery()
        ;
    }

    public function typeaheadQuery(Podcast $podcast, string $q) : Query {
        return $this->createQueryBuilder('publisher')
            ->andWhere('publisher.podcast = :p')
            ->andWhere('publisher.name LIKE :q')
            ->orderBy('publisher.name', 'ASC')
            ->setParameter('q', "%{$q}%")
            ->setParameter('p', $podcast->getId())
            ->getQuery()
        ;
    }

    public function searchQuery(Podcast $podcast, string $q) : Query {
        return $this->createQueryBuilder('publisher')
            ->addSelect('MATCH (publisher.name, publisher.description) AGAINST(:q BOOLEAN) as HIDDEN score')
            ->andWhere('publisher.podcast = :p')
            ->andHaving('score > 0')
            ->orderBy('score', 'DESC')
            ->addOrderBy('publisher.name', 'ASC')
            ->setParameter('q', $q)
            ->setParameter('p', $podcast->getId())
            ->getQuery()
        ;
    }
}
