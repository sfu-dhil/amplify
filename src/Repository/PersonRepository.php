<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Person;
use App\Entity\Podcast;
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

    public function indexQuery(Podcast $podcast) : Query {
        return $this->createQueryBuilder('person')
            ->andWhere('person.podcast = :p')
            ->orderBy('person.sortableName', 'ASC')
            ->setParameter('p', $podcast->getId())
            ->getQuery()
        ;
    }

    public function typeaheadQuery(Podcast $podcast, string $q) : Query {
        return $this->createQueryBuilder('person')
            ->andWhere('person.fullname LIKE :q')
            ->andWhere('person.podcast = :p')
            ->orderBy('person.sortableName', 'ASC')
            ->setParameter('q', "%{$q}%")
            ->setParameter('p', $podcast->getId())
            ->getQuery()
        ;
    }

    public function searchQuery(Podcast $podcast, string $q) : Query {
        return $this->createQueryBuilder('person')
            ->addSelect('MATCH (person.fullname, person.bio) AGAINST(:q BOOLEAN) as HIDDEN score')
            ->andWhere('person.podcast = :p')
            ->andHaving('score > 0')
            ->orderBy('score', 'DESC')
            ->addOrderBy('person.sortableName', 'ASC')
            ->setParameter('q', $q)
            ->setParameter('p', $podcast->getId())
            ->getQuery()
        ;
    }
}
