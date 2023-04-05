<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Podcast;
use App\Entity\Season;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Season find($id, $lockMode = null, $lockVersion = null)
 * @method Season[] findAll()
 * @method Season[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Season findOneBy(array $criteria, array $orderBy = null)
 *
 * @phpstan-extends ServiceEntityRepository<Season>
 */
class SeasonRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Season::class);
    }

    public function typeaheadQuery(Podcast $podcast, string $q) : Query {
        return $this->createQueryBuilder('season')
            ->andWhere('season.podcast = :p')
            ->andWhere('season.title LIKE :q')
            ->orderBy('season.title', 'ASC')
            ->setParameter('p', $podcast->getId())
            ->setParameter('q', "%{$q}%")
            ->getQuery()
        ;
    }
}
