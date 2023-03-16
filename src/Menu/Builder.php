<?php

declare(strict_types=1);

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

    private function hasRole(string $role) : bool {
        if ( ! $this->tokenStorage->getToken()) {
            return false;
        }

        return $this->authChecker->isGranted($role);
    }

    /**
     * Build a menu for navigation.
     *
     * @param array<string,mixed> $options
     *
     * @return ItemInterface
     */
    public function mainMenu(array $options) {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttributes([
            'class' => 'navbar-nav',
        ]);

        $browse = $menu->addChild('Browse', [
            'uri' => '#',
            'label' => 'Browse',
            'attributes' => [
                'class' => 'nav-item dropdown',
            ],
            'linkAttributes' => [
                'class' => 'nav-link dropdown-toggle',
                'role' => 'button',
                'data-bs-toggle' => 'dropdown',
                'id' => 'browse-dropdown',
            ],
            'childrenAttributes' => [
                'class' => 'dropdown-menu',
                'aria-labelledby' => 'browse-dropdown',
            ],
        ]);

        $browse->addChild('Contributor Roles', [
            'route' => 'contributor_role_index',
            'linkAttributes' => [
                'class' => 'dropdown-item',
            ],
        ]);
        $browse->addChild('Episodes', [
            'route' => 'episode_index',
            'linkAttributes' => [
                'class' => 'dropdown-item',
            ],
        ]);
        $browse->addChild('People', [
            'route' => 'person_index',
            'linkAttributes' => [
                'class' => 'dropdown-item',
            ],
        ]);
        $browse->addChild('Institutions', [
            'route' => 'institution_index',
            'linkAttributes' => [
                'class' => 'dropdown-item',
            ],
        ]);
        $browse->addChild('Podcasts', [
            'route' => 'podcast_index',
            'linkAttributes' => [
                'class' => 'dropdown-item',
            ],
        ]);
        $browse->addChild('Publishers', [
            'route' => 'publisher_index',
            'linkAttributes' => [
                'class' => 'dropdown-item',
            ],
        ]);
        $browse->addChild('Seasons', [
            'route' => 'season_index',
            'linkAttributes' => [
                'class' => 'dropdown-item',
            ],
        ]);
        if ($this->hasRole('ROLE_CONTENT_ADMIN')) {
            $divider = $browse->addChild('divider_content', [
                'label' => '',
                'attributes' => [
                    'class' => 'dropdown-divider',
                ],
            ]);
            $browse->addChild('Categories', [
                'route' => 'category_index',
                'linkAttributes' => [
                    'class' => 'dropdown-item',
                ],
            ]);
            $browse->addChild('Languages', [
                'route' => 'language_index',
                'linkAttributes' => [
                    'class' => 'dropdown-item',
                ],
            ]);
        }

        if ($this->hasRole('ROLE_ADMIN')) {
            $divider = $browse->addChild('divider_admin', [
                'label' => '',
                'attributes' => [
                    'class' => 'dropdown-divider',
                ],
            ]);
            $browse->addChild('Contributions', [
                'route' => 'contribution_index',
                'linkAttributes' => [
                    'class' => 'dropdown-item',
                ],
            ]);
        }

        return $menu;
    }
}
