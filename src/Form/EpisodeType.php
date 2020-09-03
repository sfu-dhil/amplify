<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Form;

use App\Entity\Episode;
use App\Entity\Language;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

/**
 * Episode form.
 */
class EpisodeType extends AbstractType {
    /**
     * Add form fields to $builder.
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        $builder->add('number', null, [
            'label' => 'Number',
            'required' => true,
            'attr' => [
                'help_block' => '',
            ],
        ]);
        $builder->add('date', DateType::class, [
            'label' => 'Publication Date',
            'html5' => true,
            'widget' => 'single_text',
            'required' => true,
            'attr' => [
                'help_block' => '',
            ],
        ]);
        $builder->add('runTime', null, [
            'label' => 'Run Time',
            'required' => true,
            'attr' => [
                'help_block' => '',
            ],
        ]);
        $builder->add('title', TextType::class, [
            'label' => 'Title',
            'required' => true,
            'attr' => [
                'help_block' => '',
            ],
        ]);
        $builder->add('alternativeTitle', TextType::class, [
            'label' => 'Alternative Title',
            'required' => false,
            'attr' => [
                'help_block' => '',
            ],
        ]);
        $builder->add('languages', Select2EntityType::class, [
            'multiple' => true,
            'remote_route' => 'language_typeahead',
            'class' => Language::class,
            'primary_key' => 'id',
            'text_property' => 'label',
            'page_limit' => 10,
            'allow_clear' => true,
            'delay' => 250,
            'attr' => [
                'help_block' => 'List of languages used in the episode',
            ],
        ]);
        $builder->add('tags', CollectionType::class, [
            'label' => 'Tags',
            'required' => true,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'entry_type' => TextType::class,
            'entry_options' => [
                'label' => false,
            ],
            'by_reference' => false,
            'attr' => [
                'class' => 'collection collection-simple',
                'help_block' => 'A URL link to the specificed publication',
            ],
        ]);
        $builder->add('references', TextareaType::class, [
            'label' => 'References',
            'required' => true,
            'attr' => [
                'help_block' => '',
                'class' => 'tinymce',
            ],
        ]);
        $builder->add('copyright', TextareaType::class, [
            'label' => 'Copyright',
            'required' => true,
            'attr' => [
                'help_block' => '',
                'class' => 'tinymce',
            ],
        ]);
        $builder->add('transcript', TextareaType::class, [
            'label' => 'Transcript',
            'required' => true,
            'attr' => [
                'help_block' => '',
                'class' => 'tinymce',
            ],
        ]);
        $builder->add('abstract', TextareaType::class, [
            'label' => 'Abstract',
            'required' => true,
            'attr' => [
                'help_block' => '',
                'class' => 'tinymce',
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
