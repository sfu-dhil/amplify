<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Podcast;
use App\Entity\Publisher;
use Nines\MediaBundle\Form\ImageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

/**
 * Podcast form.
 */
class PodcastType extends AbstractType {
    public function __construct(
        public UrlGeneratorInterface $router,
    ) {
    }

    /**
     * Add form fields to $builder.
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        $builder->add('title', TextType::class, [
            'label' => 'Title',
            'required' => true,
        ]);
        $builder->add('subTitle', TextType::class, [
            'label' => 'Subtitle',
            'required' => false,
        ]);
        $builder->add('explicit', ChoiceType::class, [
            'label' => 'Explicit',
            'expanded' => false,
            'multiple' => false,
            'choices' => [
                'Yes' => true,
                'No' => false,
            ],
            'required' => true,
        ]);
        $builder->add('website', UrlType::class, [
            'label' => 'Website',
            'required' => true,
        ]);
        $builder->add('rss', UrlType::class, [
            'label' => 'Rss',
            'required' => true,
        ]);
        $builder->add('languageCode', LanguageType::class, [
            'label' => 'Primary Language',
            'expanded' => false,
            'multiple' => false,
            'preferred_choices' => ['en', 'fr'],
        ]);
        $builder->add('description', TextareaType::class, [
            'label' => 'Description',
            'required' => true,
            'attr' => [
                'class' => 'tinymce',
            ],
        ]);
        $builder->add('copyright', TextareaType::class, [
            'label' => 'Copyright',
            'required' => true,
            'help' => 'Suggested text: "Rights remain with the creators."',
            'attr' => [
                'class' => 'tinymce',
            ],
        ]);
        $builder->add('license', TextareaType::class, [
            'label' => 'License',
            'required' => false,
            'help' => 'Optional. See <a href="https://creativecommons.org/about/cclicenses/">CreativeCommons.org</a> for suggestions',
            'help_html' => true,
            'attr' => [
                'class' => 'tinymce',
            ],
        ]);
        $builder->add('publisher', Select2EntityType::class, [
            'label' => 'Publisher',
            'class' => Publisher::class,
            'remote_route' => 'publisher_typeahead',
            'allow_clear' => true,
            'attr' => [
                'add_path' => 'publisher_new',
                'add_label' => 'Add Publisher',
            ],
        ]);
        $builder->add('contributions', CollectionType::class, [
            'label' => 'Contributions',
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'entry_type' => ContributionType::class,
            'entry_options' => [
                'label' => false,
            ],
            'by_reference' => false,
            'attr' => [
                'class' => 'collection collection-complex',
            ],
        ]);
        $builder->add('categories', CollectionType::class, [
            'label' => 'Categories',
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'entry_type' => ChoiceType::class,
            'entry_options' => [
                'label' => false,
                'choices' => array_reduce($builder->getData()->getAllItunesCategories(), function ($result, $item) {
                    $result[$item] = $item;

                    return $result;
                }),
            ],
            'prototype' => true,
            'by_reference' => false,
            'attr' => [
                'class' => 'collection collection-simple',
            ],
        ]);

        $builder->add('images', CollectionType::class, [
            'label' => 'Images',
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'entry_type' => ImageType::class,
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
            'data_class' => Podcast::class,
        ]);
    }
}
