<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Repository;

use App\Entity\Episode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Episode find($id, $lockMode = null, $lockVersion = null)
 * @method null|Episode findOneBy(array $criteria, array $orderBy = null)
 * @method Episode[]    findAll()
 * @method Episode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EpisodeRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Episode::class);
    }

    /**
     * @return Query
     */
    public function indexQuery() {
        return $this->createQueryBuilder('episode')
            ->orderBy('episode.id')
            ->getQuery()
        ;
    }

    /**
     * @param string $q
     *
     * @return Collection|Episode[]
     */
    public function typeaheadSearch($q) {
        throw new \RuntimeException('Not implemented yet.');
        $qb = $this->createQueryBuilder('episode');
        $qb->andWhere('episode.column LIKE :q');
        $qb->orderBy('episode.column', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery()->execute();
    }
}
