<?php

namespace App\Form;

use App\Entity\Category;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

/**
 * Category form.
 */
class CategoryType extends AbstractType {

    /**
     * Add form fields to $builder.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
                                    $builder->add('name', TextType::class, array(
                    'label' => 'Name',
                    'required' => true,
                    'attr' => array(
                        'help_block' => '',
                    ),
                ));
                                        $builder->add('label', TextType::class, array(
                    'label' => 'Label',
                    'required' => true,
                    'attr' => array(
                        'help_block' => '',
                    ),
                ));
                                        $builder->add('description', TextareaType::class, array(
                    'label' => 'Description',
                    'required' => false,
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
            'data_class' => Category::class
        ));
    }

}
