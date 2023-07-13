<?php

declare(strict_types=1);

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Nines\UserBundle\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserExtraFixtures extends Fixture implements FixtureGroupInterface {
    public const USER_WITH_ACCESS = [
        'username' => 'user_access@example.com',
        'password' => 'secret_2',
    ];

    private ?UserPasswordHasherInterface $passwordHasher = null;

    public function __construct(UserPasswordHasherInterface $passwordHasher) {
        $this->passwordHasher = $passwordHasher;
    }

    public static function getGroups() : array {
        return ['dev', 'test'];
    }

    public function load(ObjectManager $manager) : void {
        $user_access = new User();
        $user_access->setEmail(self::USER_WITH_ACCESS['username']);
        $user_access->setFullname('user with access');
        $user_access->setAffiliation('Department');
        $user_access->setPassword($this->passwordHasher->hashPassword($user_access, self::USER_WITH_ACCESS['password']));
        $user_access->setActive(true);
        $this->setReference('user.user_access', $user_access);
        $manager->persist($user_access);

        $manager->flush();
    }
}
