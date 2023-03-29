<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Language;
use App\Entity\Podcast;
use App\Entity\Publisher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

/**
 * Podcast form.
 */
class PodcastType extends AbstractType {
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
            'expanded' => true,
            'multiple' => false,
            'choices' => [
                'Yes' => true,
                'No' => false,
            ],
            'required' => true,
        ]);
        $builder->add('description', TextareaType::class, [
            'label' => 'Description',
            'required' => true,
            'attr' => [
                'class' => 'tinymce',
            ],
        ]);
        $builder->add('language', Select2EntityType::class, [
            'label' => 'Primary Language',
            'class' => Language::class,
            'remote_route' => 'language_typeahead',
            'allow_clear' => true,
            'attr' => [
                'add_path' => 'language_new_popup',
                'add_label' => 'Add Language',
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
            'required' => true,
            'help' => 'Optional. See <a href="https://creativecommons.org/about/cclicenses/">CreativeCommons.org</a> for suggestions',
            'help_html' => true,
            'attr' => [
                'class' => 'tinymce',
            ],
        ]);
        $builder->add('website', UrlType::class, [
            'label' => 'Website',
            'required' => true,
        ]);
        $builder->add('rss', UrlType::class, [
            'label' => 'Rss',
            'required' => true,
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
            'data_class' => Podcast::class,
        ]);
    }
}
