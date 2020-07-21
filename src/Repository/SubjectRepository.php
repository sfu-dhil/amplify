<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Repository;

use App\Entity\Subject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Subject find($id, $lockMode = null, $lockVersion = null)
 * @method null|Subject findOneBy(array $criteria, array $orderBy = null)
 * @method Subject[]    findAll()
 * @method Subject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubjectRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Subject::class);
    }

    /**
     * @return Query
     */
    public function indexQuery() {
        return $this->createQueryBuilder('subject')
            ->orderBy('subject.id')
            ->getQuery()
        ;
    }

    /**
     * @param string $q
     *
     * @return Collection|Subject[]
     */
    public function typeaheadSearch($q) {
        throw new \RuntimeException('Not implemented yet.');
        $qb = $this->createQueryBuilder('subject');
        $qb->andWhere('subject.column LIKE :q');
        $qb->orderBy('subject.column', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery()->execute();
    }
}
