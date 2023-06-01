<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Episode;
use App\Entity\Season;
use Nines\MediaBundle\Form\AudioType;
use Nines\MediaBundle\Form\ImageType;
use Nines\MediaBundle\Form\PdfType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

/**
 * Episode form.
 */
class EpisodeType extends AbstractType {
    public function __construct(
        public UrlGeneratorInterface $router,
    ) {
    }

    /**
     * Add form fields to $builder.
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        $builder->add('season', Select2EntityType::class, [
            'label' => 'Season',
            'class' => Season::class,
            'remote_route' => 'season_typeahead',
            'remote_params' => ['podcast_id' => $builder->getData()->getPodcast()->getId()],
            'allow_clear' => true,
            'attr' => [
                'add_route' => $this->router->generate('season_new', ['podcast_id' => $builder->getData()->getPodcast()->getId()]),
                'add_label' => 'Add Season',
            ],
        ]);
        $builder->add('episodeType', ChoiceType::class, [
            'label' => 'Episode Type',
            'expanded' => false,
            'multiple' => false,
            'choices' => [
                'Full' => 'full',
                'Bonus' => 'bonus',
                'Trailer' => 'trailer',
            ],
            'required' => true,
        ]);
        $builder->add('number', null, [
            'label' => 'Episode Number',
            'required' => true,
        ]);
        $builder->add('title', TextType::class, [
            'label' => 'Title',
            'required' => true,
        ]);
        $builder->add('subTitle', TextType::class, [
            'label' => 'Alternative Title',
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
            'required' => false,
        ]);
        $builder->add('date', DateType::class, [
            'label' => 'Date',
            'required' => true,
            'widget' => 'single_text',
            'html5' => true,
        ]);
        $builder->add('runTime', TimeType::class, [
            'label' => 'Run Time',
            'required' => true,
            'input' => 'string',
            'html5' => false,
            'widget' => 'single_text',
            'with_seconds' => true,
            'help' => 'Runtime in hh:mm:ss format',
        ]);
        $builder->add('description', TextareaType::class, [
            'label' => 'Description',
            'required' => true,
            'attr' => [
                'class' => 'tinymce',
            ],
        ]);
        $builder->add('bibliography', TextareaType::class, [
            'label' => 'Bibliography',
            'required' => false,
            'attr' => [
                'class' => 'tinymce',
            ],
        ]);
        $builder->add('transcript', TextareaType::class, [
            'label' => 'Transcript',
            'required' => false,
            'attr' => [
                'class' => 'tinymce',
            ],
        ]);
        $builder->add('permissions', TextareaType::class, [
            'label' => 'Permissions',
            'required' => false,
            'attr' => [
                'class' => 'tinymce',
            ],
        ]);
        $builder->add('subjects', CollectionType::class, [
            'label' => 'Subject',
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'entry_type' => TextType::class,
            'entry_options' => [
                'label' => false,
            ],
            'attr' => [
                'class' => 'collection collection-simple oclcfast',
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
        $builder->add('audios', CollectionType::class, [
            'label' => 'Audio',
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'entry_type' => AudioType::class,
            'entry_options' => [
                'label' => false,
            ],
            'prototype' => true,
            'by_reference' => false,
            'attr' => [
                'class' => 'collection collection-media collection-media-audio',
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
        $builder->add('pdfs', CollectionType::class, [
            'label' => 'Transcripts',
            'required' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'entry_type' => PdfType::class,
            'entry_options' => [
                'label' => false,
            ],
            'prototype' => true,
            'by_reference' => false,
            'attr' => [
                'class' => 'collection collection-media collection-media-pdf',
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
            'data_class' => Episode::class,
        ]);
    }
}
