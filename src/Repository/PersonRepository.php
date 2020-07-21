<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Repository;

use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Person find($id, $lockMode = null, $lockVersion = null)
 * @method null|Person findOneBy(array $criteria, array $orderBy = null)
 * @method Person[]    findAll()
 * @method Person[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Person::class);
    }

    /**
     * @return Query
     */
    public function indexQuery() {
        return $this->createQueryBuilder('person')
            ->orderBy('person.id')
            ->getQuery()
        ;
    }

    /**
     * @param string $q
     *
     * @return Collection|Person[]
     */
    public function typeaheadSearch($q) {
        throw new \RuntimeException('Not implemented yet.');
        $qb = $this->createQueryBuilder('person');
        $qb->andWhere('person.column LIKE :q');
        $qb->orderBy('person.column', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery()->execute();
    }
}
