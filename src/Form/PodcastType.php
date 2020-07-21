<?php

namespace App\Form;

use App\Entity\Podcast;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

/**
 * Podcast form.
 */
class PodcastType extends AbstractType {

    /**
     * Add form fields to $builder.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
                                        $builder->add('explicit', ChoiceType::class, array(
                    'label' => 'Explicit',
                    'expanded' => true,
                    'multiple' => false,
                    'choices' => array(
                        'Yes' => true,
                        'No' => false,
                        ),
                    'required' => true,
                    'attr' => array(
                        'help_block' => '',
                    ),
                ));
                
                                        $builder->add('description', TextareaType::class, array(
                    'label' => 'Description',
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
                                        $builder->add('category', TextType::class, array(
                    'label' => 'Category',
                    'required' => true,
                    'attr' => array(
                        'help_block' => '',
                    ),
                ));
                                        $builder->add('website', TextareaType::class, array(
                    'label' => 'Website',
                    'required' => true,
                    'attr' => array(
                        'help_block' => '',
                        'class' => 'tinymce',
                    ),
                ));
                                        $builder->add('rss', TextType::class, array(
                    'label' => 'Rss',
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
            'data_class' => Podcast::class
        ));
    }

}
