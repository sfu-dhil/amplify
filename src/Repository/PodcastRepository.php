<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Podcast;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Nines\UserBundle\Entity\User;

/**
 * @method null|Podcast find($id, $lockMode = null, $lockVersion = null)
 * @method Podcast[] findAll()
 * @method Podcast[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Podcast findOneBy(array $criteria, array $orderBy = null)
 *
 * @phpstan-extends ServiceEntityRepository<Podcast>
 */
class PodcastRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Podcast::class);
    }

    private function index() : QueryBuilder {
        return $this->createQueryBuilder('podcast')
            ->orderBy('podcast.title', 'ASC')
        ;
    }

    private function search(string $q) : QueryBuilder {
        return $this->createQueryBuilder('podcast')
            ->addSelect('MATCH (podcast.title, podcast.subTitle, podcast.description) AGAINST(:q BOOLEAN) as HIDDEN score')
            ->andHaving('score > 0')
            ->orderBy('score', 'DESC')
            ->addOrderBy('podcast.title', 'ASC')
            ->setParameter('q', $q)
        ;
    }

    public function indexUserQuery(User $user) : Query {
        return $this->index()
            ->join('podcast.shares', 'share')
            ->andWhere('share.user = :u')
            ->setParameter('u', $user->getId())
            ->getQuery()
        ;
    }

    public function indexQuery() : Query {
        return $this->index()->getQuery();
    }

    public function searchUserQuery(User $user, string $q) : Query {
        return $this->search($q)
            ->join('podcast.shares', 'share')
            ->andWhere('share.user = :u')
            ->setParameter('u', $user->getId())
            ->getQuery()
        ;
    }

    public function searchQuery(string $q) : Query {
        return $this->search($q)->getQuery();
    }
}
