<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Podcast;
use App\Entity\Publisher;
use App\Entity\Season;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

/**
 * Season form.
 */
class SeasonType extends AbstractType {
    /**
     * Add form fields to $builder.
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        $builder->add('number', null, [
            'label' => 'Number',
            'required' => false,
        ]);
        $builder->add('preserved', ChoiceType::class, [
            'label' => 'Preserved',
            'expanded' => true,
            'multiple' => false,
            'choices' => [
                'Yes' => true,
                'No' => false,
            ],
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
        $builder->add('description', TextareaType::class, [
            'label' => 'Description',
            'required' => true,
            'attr' => [
                'class' => 'tinymce',
            ],
        ]);

        $builder->add('podcast', Select2EntityType::class, [
            'label' => 'Podcast',
            'class' => Podcast::class,
            'remote_route' => 'podcast_typeahead',
            'allow_clear' => true,
            'required' => true,
            'attr' => [
                'add_path' => 'podcast_new_popup',
                'add_label' => 'Add Podcast',
            ],
        ]);

        $builder->add('publisher', Select2EntityType::class, [
            'label' => 'Publisher',
            'class' => Publisher::class,
            'remote_route' => 'publisher_typeahead',
            'allow_clear' => true,
            'attr' => [
                'add_path' => 'publisher_new_popup',
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
