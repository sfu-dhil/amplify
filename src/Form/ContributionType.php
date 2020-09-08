<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Form;

use App\Entity\Contribution;
use App\Entity\ContributorRole;
use App\Entity\Episode;
use App\Entity\Person;
use App\Entity\Podcast;
use App\Entity\Season;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

/**
 * Contribution form.
 */
class ContributionType extends AbstractType {
    /**
     * Add form fields to $builder.
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        $builder->add('person', Select2EntityType::class, [
            'label' => 'Person',
            'class' => Person::class,
            'remote_route' => 'person_typeahead',
            'allow_clear' => true,
            'attr' => [
                'help_block' => '',
                'add_path' => 'person_new_popup',
                'add_label' => 'Add Person',
            ],
        ]);

        $builder->add('contributorRole', Select2EntityType::class, [
            'label' => 'ContributorRole',
            'class' => ContributorRole::class,
            'remote_route' => 'contributor_role_typeahead',
            'allow_clear' => true,
            'attr' => [
                'help_block' => '',
                'add_path' => 'contributor_role_new_popup',
                'add_label' => 'Add ContributorRole',
            ],
        ]);

        $builder->add('podcast', Select2EntityType::class, [
            'label' => 'Podcast',
            'class' => Podcast::class,
            'remote_route' => 'podcast_typeahead',
            'allow_clear' => true,
            'attr' => [
                'help_block' => '',
                'add_path' => 'podcast_new_popup',
                'add_label' => 'Add Podcast',
            ],
        ]);

        $builder->add('season', Select2EntityType::class, [
            'label' => 'Season',
            'class' => Season::class,
            'remote_route' => 'season_typeahead',
            'allow_clear' => true,
            'attr' => [
                'help_block' => '',
                'add_path' => 'season_new_popup',
                'add_label' => 'Add Season',
            ],
        ]);

        $builder->add('episode', Select2EntityType::class, [
            'label' => 'Episode',
            'class' => Episode::class,
            'remote_route' => 'episode_typeahead',
            'allow_clear' => true,
            'attr' => [
                'help_block' => '',
                'add_path' => 'episode_new_popup',
                'add_label' => 'Add Episode',
            ],
        ]);
    }

    /**
     * Define options for the form.
     *
     * Set default, optional, and required options passed to the
     * buildForm() method via the $options parameter.
     */
    public function configureOptions(OptionsResolver $resolver) : void {
        $resolver->setDefaults([
            'data_class' => Contribution::class,
        ]);
    }
}
