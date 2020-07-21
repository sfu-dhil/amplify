<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Repository;

use App\Entity\Season;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Season find($id, $lockMode = null, $lockVersion = null)
 * @method null|Season findOneBy(array $criteria, array $orderBy = null)
 * @method Season[]    findAll()
 * @method Season[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeasonRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Season::class);
    }

    /**
     * @return Query
     */
    public function indexQuery() {
        return $this->createQueryBuilder('season')
            ->orderBy('season.id')
            ->getQuery()
        ;
    }

    /**
     * @param string $q
     *
     * @return Collection|Season[]
     */
    public function typeaheadSearch($q) {
        throw new \RuntimeException('Not implemented yet.');
        $qb = $this->createQueryBuilder('season');
        $qb->andWhere('season.column LIKE :q');
        $qb->orderBy('season.column', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery()->execute();
    }
}
