<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Repository;

use App\Entity\Institution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Institution find($id, $lockMode = null, $lockVersion = null)
 * @method null|Institution findOneBy(array $criteria, array $orderBy = null)
 * @method Institution[]    findAll()
 * @method Institution[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InstitutionRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Institution::class);
    }

    /**
     * @return Query
     */
    public function indexQuery() {
        return $this->createQueryBuilder('institution')
            ->orderBy('institution.id')
            ->getQuery()
        ;
    }

    /**
     * @param string $q
     *
     * @return Collection|Institution[]
     */
    public function typeaheadQuery($q) {
        $qb = $this->createQueryBuilder('institution');
        $qb->andWhere('institution.name LIKE :q');
        $qb->orderBy('institution.name', 'ASC');
        $qb->addOrderBy('institution.province', 'ASC');
        $qb->setParameter('q', "%{$q}%");

        return $qb->getQuery()->execute();
    }

    /**
     * @param string $q
     *
     * @return Collection|Institution[]
     */
    public function searchQuery($q) {
        $qb = $this->createQueryBuilder('institution');
        $qb->andWhere('institution.name LIKE :q');
        $qb->orderBy('institution.name', 'ASC');
        $qb->addOrderBy('institution.province', 'ASC');
        $qb->setParameter('q', "%{$q}%");

        return $qb->getQuery()->execute();
    }
}
