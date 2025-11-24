<?php

declare(strict_types=1);

namespace App\Menu;

use App\Repository\PodcastRepository;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Nines\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
        private ParameterBagInterface $parameterBagInterface,
    ) {}

    private function hasRole(string $role) : bool {
        if ( ! $this->tokenStorage->getToken()) {
            return false;
        }

        return $this->authChecker->isGranted($role);
    }

    protected function getUser() : ?User {
        if ( ! $this->hasRole('ROLE_USER')) {
            return null;
        }
        $user = $this->tokenStorage->getToken()->getUser();
        if ( ! $user instanceof User) {
            return null;
        }

        return $user;
    }

    public function mainSidebarMenu(array $options) : ItemInterface {

        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttributes([
            'class' => 'list-unstyled ps-0',
            'aria-label' => 'Sidebar: List of Podcasts, Documentation, and Privacy',
        ]);

        $menu->addChild('divider1', [
            'label' => '<hr>',
            'attributes' => [
                'aria-hidden' => 'true',
            ],
            'extras' => [
                'safe_label' => true,
            ],
        ]);

        if ($this->hasRole('ROLE_USER')) {
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
                    'aria-hidden' => 'true',
                    'aria-label' => 'Toggle List of Podcasts',
                ],
                'childrenAttributes' => [
                    'class' => 'collapse show btn-toggle-nav list-unstyled fw-normal pb-1 small',
                    'aria-label' => 'List of Podcasts',
                    'id' => 'podcast-collapse',
                ],
            ]);

            $podcasts = ($this->hasRole('ROLE_ADMIN') ? $this->podcastRepository->indexQuery() : $this->podcastRepository->indexUserQuery($this->getUser()))
                ->setMaxResults(5)
                ->getResult()
            ;
            foreach ($podcasts as $podcast) {
                $podcastsMenu->addChild("Podcast_{$podcast->getId()}", [
                    'label' => $podcast->getTitle(),
                    'route' => 'podcast_show',
                    'routeParameters' => ['id' => $podcast->getId()],
                    'linkAttributes' => [
                        'aria-label' => "View Podcast: {$podcast->getTitle()}",
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
                'attributes' => [
                    'aria-hidden' => 'true',
                ],
                'extras' => [
                    'safe_label' => true,
                ],
            ]);
        }

        $menu->addChild('Documentation', [
            'uri' => $this->parameterBagInterface->get('router.request_context.base_url') . '/docs/',
            'attributes' => [
                'class' => 'mb-1',
            ],
            'linkAttributes' => [
                'target' => '_blank',
                'class' => 'btn fw-bold d-inline-flex align-items-center rounded border-0',
            ],
        ]);

        $menu->addChild('Privacy', [
            'uri' => 'https://docs.dhil.lib.sfu.ca/privacy.html',
            'attributes' => [
                'class' => 'mb-1',
            ],
            'linkAttributes' => [
                'class' => 'btn fw-bold d-inline-flex align-items-center rounded border-0',
                'target' => '_blank',
            ],
        ]);

        return $menu;
    }
}
