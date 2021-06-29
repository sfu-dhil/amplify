<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Transcript;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Transcript|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transcript|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transcript[]    findAll()
 * @method Transcript[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TranscriptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transcript::class);
    }

    /**
     * @return Query
     */
    public function indexQuery() {
        return $this->createQueryBuilder('transcript')
            ->orderBy('transcript.id')
            ->getQuery();
    }

    /**
     * @param string $q
     *
     * @return Collection|Transcript[]
     */
    public function typeaheadQuery($q) {
        throw new \RuntimeException("Not implemented yet.");
        $qb = $this->createQueryBuilder('transcript');
        $qb->andWhere('transcript.column LIKE :q');
        $qb->orderBy('transcript.column', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery()->execute();
    }

    
}
