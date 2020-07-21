<?php

namespace App\Form;

use App\Entity\Episode;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

/**
 * Episode form.
 */
class EpisodeType extends AbstractType {

    /**
     * Add form fields to $builder.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
                                    $builder->add('number', null, [
                    'label' => 'Number',
                    'required' => true,
                    'attr' => array(
                        'help_block' => '',
                    ),
                ]);
                                        $builder->add('date', null, [
                    'label' => 'Date',
                    'required' => true,
                    'attr' => array(
                        'help_block' => '',
                    ),
                ]);
                                        $builder->add('runTime', null, [
                    'label' => 'Run Time',
                    'required' => true,
                    'attr' => array(
                        'help_block' => '',
                    ),
                ]);
                                        $builder->add('title', TextType::class, array(
                    'label' => 'Title',
                    'required' => true,
                    'attr' => array(
                        'help_block' => '',
                    ),
                ));
                                        $builder->add('alternativeTitle', TextType::class, array(
                    'label' => 'Alternative Title',
                    'required' => false,
                    'attr' => array(
                        'help_block' => '',
                    ),
                ));
                                        $builder->add('language', TextType::class, array(
                    'label' => 'Language',
                    'required' => true,
                    'attr' => array(
                        'help_block' => '',
                    ),
                ));
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
                                        $builder->add('references', TextareaType::class, array(
                    'label' => 'References',
                    'required' => true,
                    'attr' => array(
                        'help_block' => '',
                        'class' => 'tinymce',
                    ),
                ));
                                        $builder->add('copyright', TextareaType::class, array(
                    'label' => 'Copyright',
                    'required' => true,
                    'attr' => array(
                        'help_block' => '',
                        'class' => 'tinymce',
                    ),
                ));
                                        $builder->add('transcript', TextareaType::class, array(
                    'label' => 'Transcript',
                    'required' => true,
                    'attr' => array(
                        'help_block' => '',
                        'class' => 'tinymce',
                    ),
                ));
                                        $builder->add('abstract', TextareaType::class, array(
                    'label' => 'Abstract',
                    'required' => true,
                    'attr' => array(
                        'help_block' => '',
                        'class' => 'tinymce',
                    ),
                ));
            
    }

    /**
     * Define options for the form.
     *
     * Set default, optional, and required options passed to the
     * buildForm() method via the $options parameter.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Episode::class
        ));
    }

}
