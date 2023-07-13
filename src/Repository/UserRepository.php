<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\ORM\Query;
use Nines\UserBundle\Repository\UserRepository as NinesUserRepository;

class UserRepository extends NinesUserRepository {
    public function typeaheadQuery(string $q) : Query {
        return $this->createQueryBuilder('user')
            ->andWhere('user.fullname LIKE :q OR user.email LIKE :q')
            ->orderBy('user.fullname', 'ASC')
            ->setParameter('q', "%{$q}%")
            ->getQuery()
        ;
    }
}
