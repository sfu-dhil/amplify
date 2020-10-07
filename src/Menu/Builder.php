<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class to build some menus for navigation.
 */
class Builder implements ContainerAwareInterface {
    use ContainerAwareTrait;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authChecker;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(FactoryInterface $factory, AuthorizationCheckerInterface $authChecker, TokenStorageInterface $tokenStorage) {
        $this->factory = $factory;
        $this->authChecker = $authChecker;
        $this->tokenStorage = $tokenStorage;
    }

    private function hasRole($role) {
        if ( ! $this->tokenStorage->getToken()) {
            return false;
        }

        return $this->authChecker->isGranted($role);
    }

    /**
     * Build a menu for navigation.
     *
     * @return ItemInterface
     */
    public function mainMenu(array $options) {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttributes([
            'class' => 'nav navbar-nav',
        ]);

        $browse = $menu->addChild('Browse', [
            'uri' => '#',
            'label' => 'Browse',
        ]);
        $browse->setAttribute('dropdown', true);
        $browse->setLinkAttribute('class', 'dropdown-toggle');
        $browse->setLinkAttribute('data-toggle', 'dropdown');
        $browse->setChildrenAttribute('class', 'dropdown-menu');

        $browse->addChild('Contributor Roles', [
            'route' => 'contributor_role_index',
        ]);
        $browse->addChild('Episodes', [
            'route' => 'episode_index',
        ]);
        $browse->addChild('People', [
            'route' => 'person_index',
        ]);
        $browse->addChild('Institutions', [
            'route' => 'institution_index',
        ]);
        $browse->addChild('Podcasts', [
            'route' => 'podcast_index',
        ]);
        $browse->addChild('Publishers', [
            'route' => 'publisher_index',
        ]);
        $browse->addChild('Seasons', [
            'route' => 'season_index',
        ]);

        if ($this->hasRole('ROLE_CONTENT_ADMIN')) {
            $divider = $browse->addChild('divider_content', [
                'label' => '',
            ]);
            $divider->setAttributes([
                'role' => 'separator',
                'class' => 'divider',
            ]);
            $browse->addChild('Categories', [
                'route' => 'category_index',
            ]);
            $browse->addChild('Languages', [
                'route' => 'language_index',
            ]);
        }

        if ($this->hasRole('ROLE_ADMIN')) {
            $divider = $browse->addChild('divider_admin', [
                'label' => '',
            ]);
            $divider->setAttributes([
                'role' => 'separator',
                'class' => 'divider',
            ]);
            $browse->addChild('Audio Files', [
                'route' => 'audio_index',
            ]);
            $browse->addChild('Contributions', [
                'route' => 'contribution_index',
            ]);
        }

        return $menu;
    }
}
