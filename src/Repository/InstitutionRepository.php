<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Institution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Institution find($id, $lockMode = null, $lockVersion = null)
 * @method Institution[] findAll()
 * @method Institution[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Institution findOneBy(array $criteria, array $orderBy = null)
 *
 * @phpstan-extends ServiceEntityRepository<Institution>
 */
class InstitutionRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Institution::class);
    }

    public function indexQuery() : Query {
        return $this->createQueryBuilder('institution')
            ->orderBy('institution.name', 'ASC')
            ->addOrderBy('institution.country', 'ASC')
            ->getQuery()
        ;
    }

    public function typeaheadQuery(string $q) : Query {
        return $this->createQueryBuilder('institution')
            ->andWhere('institution.name LIKE :q')
            ->orderBy('institution.name', 'ASC')
            ->addOrderBy('institution.country', 'ASC')
            ->setParameter('q', "%{$q}%")
            ->getQuery()
        ;
    }

    public function searchQuery(string $q) : Query {
        return $this->createQueryBuilder('institution')
            ->addSelect('MATCH (institution.name) AGAINST(:q BOOLEAN) as HIDDEN score')
            ->andHaving('score > 0')
            ->orderBy('score', 'DESC')
            ->addOrderBy('institution.name', 'ASC')
            ->addOrderBy('institution.country', 'ASC')
            ->setParameter('q', $q)
            ->getQuery()
        ;
    }
}
