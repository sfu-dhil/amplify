<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

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
 * @phpstan-extends ServiceEntityRepository<Institution>
 */
class InstitutionRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Institution::class);
    }

    public function indexQuery() : Query {
        return $this->createQueryBuilder('institution')
            ->orderBy('institution.id')
            ->getQuery()
        ;
    }

    public function typeaheadQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('institution');
        $qb->andWhere('institution.name LIKE :q');
        $qb->orderBy('institution.name', 'ASC');
        $qb->addOrderBy('institution.country', 'ASC');
        $qb->setParameter('q', "%{$q}%");

        return $qb->getQuery();
    }

    public function searchQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('institution');
        $qb->andWhere('institution.name LIKE :q');
        $qb->orderBy('institution.name', 'ASC');
        $qb->addOrderBy('institution.country', 'ASC');
        $qb->setParameter('q', "%{$q}%");

        return $qb->getQuery();
    }
}
