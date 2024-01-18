<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Publisher;
use App\Entity\Season;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

/**
 * Season form.
 */
class SeasonType extends AbstractType {
    public function __construct(
        public UrlGeneratorInterface $router,
    ) {}

    /**
     * Add form fields to $builder.
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        $builder->add('number', null, [
            'label' => 'Season Number',
            'required' => false,
        ]);
        $builder->add('title', TextType::class, [
            'label' => 'Title',
            'required' => true,
        ]);
        $builder->add('subTitle', TextType::class, [
            'label' => 'Subtitle',
            'required' => false,
        ]);
        $builder->add('description', TextareaType::class, [
            'label' => 'Description',
            'required' => true,
            'attr' => [
                'class' => 'tinymce',
            ],
        ]);

        $builder->add('publisher', Select2EntityType::class, [
            'label' => 'Publisher',
            'class' => Publisher::class,
            'remote_route' => 'publisher_typeahead',
            'remote_params' => ['podcast_id' => $builder->getData()->getPodcast()->getId()],
            'allow_clear' => true,
            'attr' => [
                'add_route' => $this->router->generate('publisher_new', ['podcast_id' => $builder->getData()->getPodcast()->getId()]),
                'add_label' => 'Add Publisher',
                'add_modal' => true,
            ],
            'placeholder' => 'Search for an existing publisher by name',
        ]);

        $builder->add('contributions', CollectionType::class, [
            'label' => 'Contributors',
            'required' => true,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'entry_type' => ContributionType::class,
            'entry_options' => [
                'label' => false,
                'podcast' => $builder->getData()->getPodcast(),
            ],
            'by_reference' => false,
            'attr' => [
                'class' => 'collection collection-complex',
                'data-collection-label' => 'Contributor',
            ],
        ]);
        $builder->add('images', CollectionType::class, [
            'label' => 'Images',
            'required' => true,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => false,
            'entry_type' => AmplifyImageType::class,
            'entry_options' => [
                'label' => false,
            ],
            'prototype' => true,
            'by_reference' => false,
            'attr' => [
                'class' => 'collection collection-media collection-media-image',
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
            'data_class' => Season::class,
        ]);
    }
}
