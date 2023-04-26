<?php

declare(strict_types=1);

namespace App\Menu;

use App\Repository\PodcastRepository;
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

    public function __construct(
        private FactoryInterface $factory,
        private AuthorizationCheckerInterface $authChecker,
        private TokenStorageInterface $tokenStorage,
        private PodcastRepository $podcastRepository,
    ) {
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

    public function mainSidebarMenu(array $options) : ItemInterface {
        $podcasts = $this->podcastRepository->findBy(
            [],
            [
                'title' => 'ASC',
                'id' => 'ASC',
            ],
            5
        );

        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttributes([
            'class' => 'list-unstyled ps-0',
        ]);

        $menu->addChild('divider1', [
            'label' => '<hr>',
            'extras' => [
                'safe_label' => true,
            ],
        ]);

        $podcastsMenu = $menu->addChild('Podcasts', [
            'uri' => '#',
            'label' => 'Podcasts',
            'attributes' => [
                'class' => 'mb-1',
            ],
            'linkAttributes' => [
                'class' => 'btn btn-toggle d-inline-flex align-items-center rounded border-0 collapsed',
                'data-bs-toggle' => 'collapse',
                'data-bs-target' => '#podcast-collapse',
                'aria-expanded' => 'true',
            ],
            'childrenAttributes' => [
                'class' => 'collapse show btn-toggle-nav list-unstyled fw-normal pb-1 small',
                'id' => 'podcast-collapse',
            ],
        ]);

        foreach ($podcasts as $podcast) {
            $podcastsMenu->addChild("Podcast_{$podcast->getId()}", [
                'label' => $podcast->getTitle(),
                'route' => 'podcast_show',
                'routeParameters' => ['id' => $podcast->getId()],
                'linkAttributes' => [
                    'class' => 'link-dark d-block text-decoration-none rounded text-truncate',
                ],
            ]);
        }

        $podcastsMenu->addChild('Podcast_all', [
            'label' => 'View all Podcasts',
            'route' => 'podcast_index',
            'linkAttributes' => [
                'class' => 'link-dark d-inline-flex text-decoration-none rounded fw-bold',
            ],
        ]);

        $menu->addChild('divider2', [
            'label' => '<hr>',
            'extras' => [
                'safe_label' => true,
            ],
        ]);

        $podcastMetaMenu = $menu->addChild('Podcast Metadata', [
            'uri' => '#',
            'label' => 'Podcast Metadata',
            'attributes' => [
                'class' => 'mb-1',
            ],
            'linkAttributes' => [
                'class' => 'btn btn-toggle d-inline-flex align-items-center rounded border-0 collapsed',
                'data-bs-toggle' => 'collapse',
                'data-bs-target' => '#podcast-meta-collapse',
                'aria-expanded' => 'false',
            ],
            'childrenAttributes' => [
                'class' => 'collapse btn-toggle-nav list-unstyled fw-normal pb-1 small',
                'id' => 'podcast-meta-collapse',
            ],
        ]);

        $podcastMetaMenu->addChild('Categories', [
            'route' => 'category_index',
            'linkAttributes' => [
                'class' => 'link-dark d-inline-flex text-decoration-none rounded',
            ],
        ]);

        $podcastMetaMenu->addChild('Languages', [
            'route' => 'language_index',
            'linkAttributes' => [
                'class' => 'link-dark d-inline-flex text-decoration-none rounded',
            ],
        ]);

        $menu->addChild('divider3', [
            'label' => '<hr>',
            'extras' => [
                'safe_label' => true,
            ],
        ]);

        $contributorsMenu = $menu->addChild('Contributors', [
            'uri' => '#',
            'label' => 'Contributors',
            'attributes' => [
                'class' => 'mb-1',
            ],
            'linkAttributes' => [
                'class' => 'btn btn-toggle d-inline-flex align-items-center rounded border-0 collapsed',
                'data-bs-toggle' => 'collapse',
                'data-bs-target' => '#contributors-collapse',
                'aria-expanded' => 'false',
            ],
            'childrenAttributes' => [
                'class' => 'collapse btn-toggle-nav list-unstyled fw-normal pb-1 small',
                'id' => 'contributors-collapse',
            ],
        ]);

        $contributorsMenu->addChild('People', [
            'route' => 'person_index',
            'linkAttributes' => [
                'class' => 'link-dark d-inline-flex text-decoration-none rounded',
            ],
        ]);
        $contributorsMenu->addChild('Contributor Roles', [
            'route' => 'contributor_role_index',
            'linkAttributes' => [
                'class' => 'link-dark d-inline-flex text-decoration-none rounded',
            ],
        ]);
        $contributorsMenu->addChild('Publishers', [
            'route' => 'publisher_index',
            'linkAttributes' => [
                'class' => 'link-dark d-inline-flex text-decoration-none rounded',
            ],
        ]);
        $contributorsMenu->addChild('Institutions', [
            'route' => 'institution_index',
            'linkAttributes' => [
                'class' => 'link-dark d-inline-flex text-decoration-none rounded',
            ],
        ]);

        $menu->addChild('divider4', [
            'label' => '<hr>',
            'extras' => [
                'safe_label' => true,
            ],
        ]);

        $menu->addChild('Privacy', [
            'route' => 'privacy',
            'attributes' => [
                'class' => 'mb-1',
            ],
            'linkAttributes' => [
                'class' => 'btn fw-bold d-inline-flex align-items-center rounded border-0',
            ],
        ]);

        return $menu;
    }
}
