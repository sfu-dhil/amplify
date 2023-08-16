<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Podcast;
use App\Entity\Share;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Share find($id, $lockMode = null, $lockVersion = null)
 * @method Share[] findAll()
 * @method Share[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Share findOneBy(array $criteria, array $orderBy = null)
 *
 * @phpstan-extends ServiceEntityRepository<Share>
 */
class ShareRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Share::class);
    }

    public function indexQuery(Podcast $podcast) : Query {
        return $this->createQueryBuilder('share')
            ->andWhere('share.podcast = :p')
            ->orderBy('share.created', 'ASC')
            ->setParameter('p', $podcast->getId())
            ->getQuery()
        ;
    }
}
