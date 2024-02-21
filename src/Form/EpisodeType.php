<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Episode;
use App\Entity\Season;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

/**
 * Episode form.
 */
class EpisodeType extends AbstractType {
    public function __construct(
        public UrlGeneratorInterface $router,
    ) {}

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
            'placeholder' => 'Search for an existing season by title',
            'required' => true,
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
            'placeholder' => 'Select if the Episode contains explicit content or not',
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
        $builder->add('permissions', TextareaType::class, [
            'label' => 'Permissions',
            'required' => false,
            'attr' => [
                'class' => 'tinymce',
            ],
        ]);
        $builder->add('keywords', ChoiceType::class, [
            'label' => 'Keywords',
            'required' => false,
            'multiple' => true,
            'choices' => array_reduce($builder->getData()->getKeywords(), function ($result, $item) {
                $result[$item] = $item;

                return $result;
            }),
            'attr' => [
                'class' => 'select2-simple',
                'data-theme' => 'bootstrap-5',
                'data-tags' => 'true',
            ],
            'placeholder' => 'Select all keywords that apply to the episode',
            'help' => 'Please press
                <kbd><i class="bi bi-arrow-return-left" aria-hidden="true"></i> Enter</kbd> or
                <kbd><i class="bi bi-arrow-left-right" aria-hidden="true"></i> Tab</kbd>
                after entering a keyword to store it.',
            'help_html' => true,
        ]);
        $builder->add('contributions', CollectionType::class, [
            'label' => 'Contributors',
            'required' => true,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'entry_type' => ContributionType::class,
            'entry_options' => [
                'label' => 'Contributor',
                'podcast' => $builder->getData()->getPodcast(),
            ],
            'by_reference' => false,
            'attr' => [
                'class' => 'collection collection-complex',
                'data-collection-label' => 'Contributor',
            ],
        ]);
        $builder->add('audios', CollectionType::class, [
            'label' => 'Audio',
            'required' => true,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'entry_type' => AmplifyAudioType::class,
            'entry_options' => [
                'label' => 'Audio',
            ],
            'prototype' => true,
            'by_reference' => false,
            'attr' => [
                'class' => 'collection collection-complex',
                'data-collection-label' => 'Audio',
            ],
        ]);
        $builder->add('images', CollectionType::class, [
            'label' => 'Images',
            'required' => true,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'entry_type' => AmplifyImageType::class,
            'entry_options' => [
                'label' => 'Image',
            ],
            'prototype' => true,
            'by_reference' => false,
            'attr' => [
                'class' => 'collection collection-complex',
                'data-collection-label' => 'Image',
            ],
        ]);
        $builder->add('transcript', TextareaType::class, [
            'label' => 'Transcript (Plain Text)',
            'required' => false,
            'attr' => [
                'class' => 'tinymce',
            ],
        ]);
        $builder->add('pdfs', CollectionType::class, [
            'label' => 'Transcript (PDF)',
            'required' => true,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'entry_type' => AmplifyPdfType::class,
            'entry_options' => [
                'label' => 'Transcript (PDF)',
            ],
            'prototype' => true,
            'by_reference' => false,
            'attr' => [
                'class' => 'collection collection-complex',
                'data-collection-label' => 'Transcript',
            ],
        ]);

        // get dynamic keyword choices working
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) : void {
            $form = $event->getForm();
            $options = $form->get('keywords')->getConfig()->getOptions();
            $options['choices'] = array_reduce($event->getData()['keywords'] ?? [], function ($result, $item) {
                $result[$item] = $item;

                return $result;
            });
            $form->add('keywords', ChoiceType::class, $options);
        });
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
