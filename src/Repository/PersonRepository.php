<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Person find($id, $lockMode = null, $lockVersion = null)
 * @method Person[] findAll()
 * @method Person[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Person findOneBy(array $criteria, array $orderBy = null)
 *
 * @phpstan-extends ServiceEntityRepository<Person>
 */
class PersonRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Person::class);
    }

    public function indexQuery() : Query {
        return $this->createQueryBuilder('person')
            ->orderBy('person.sortableName', 'ASC')
            ->getQuery()
        ;
    }

    public function typeaheadQuery(string $q) : Query {
        return $this->createQueryBuilder('person')
            ->andWhere('person.fullname LIKE :q')
            ->orderBy('person.sortableName', 'ASC')
            ->setParameter('q', "%{$q}%")
            ->getQuery()
        ;
    }

    public function searchQuery(string $q) : Query {
        return $this->createQueryBuilder('person')
            ->addSelect('MATCH (person.fullname, person.bio) AGAINST(:q BOOLEAN) as HIDDEN score')
            ->andHaving('score > 0')
            ->orderBy('score', 'DESC')
            ->addOrderBy('person.sortableName', 'ASC')
            ->setParameter('q', $q)
            ->getQuery()
        ;
    }
}
