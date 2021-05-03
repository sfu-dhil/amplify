<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Repository;

use App\Entity\Audio;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Audio find($id, $lockMode = null, $lockVersion = null)
 * @method null|Audio findOneBy(array $criteria, array $orderBy = null)
 * @method Audio[]    findAll()
 * @method Audio[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AudioRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Audio::class);
    }

    /**
     * @return Query
     */
    public function indexQuery() {
        return $this->createQueryBuilder('audio')
            ->orderBy('audio.id')
            ->getQuery()
        ;
    }

    /**
     * @param string $q
     *
     * @return Audio[]|Collection
     */
    public function searchQuery($q) {
        $qb = $this->createQueryBuilder('audio');
        $qb->andWhere('audio.originalName LIKE :q');
        $qb->orderBy('audio.originalName', 'ASC');
        $qb->setParameter('q', "%{$q}%");

        return $qb->getQuery()->execute();
    }
}
