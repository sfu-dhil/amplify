<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Institution;
use App\Entity\Person;
use Nines\MediaBundle\Form\LinkableType;
use Nines\MediaBundle\Form\Mapper\LinkableMapper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

/**
 * Person form.
 */
class PersonType extends AbstractType {
    private ?LinkableMapper $mapper = null;

    /**
     * Add form fields to $builder.
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        $builder->add('fullname', TextType::class, [
            'label' => 'Fullname',
            'required' => true,
        ]);
        $builder->add('sortableName', TextType::class, [
            'label' => 'Sortable Name',
            'required' => true,
        ]);
        $builder->add('location', TextType::class, [
            'label' => 'Location',
            'required' => true,
        ]);
        $builder->add('bio', TextareaType::class, [
            'label' => 'Bio',
            'required' => true,
            'attr' => [
                'class' => 'tinymce',
            ],
        ]);
        $builder->add('institution', Select2EntityType::class, [
            'label' => 'Institution',
            'class' => Institution::class,
            'remote_route' => 'institution_typeahead',
            'allow_clear' => true,
            'attr' => [
                'add_path' => 'institution_new',
                'add_label' => 'Add Institution',
            ],
            'placeholder' => 'Search for an existing person by name',
        ]);
        LinkableType::add($builder, $options);
        $builder->setDataMapper($this->mapper);
    }

    #[\Symfony\Contracts\Service\Attribute\Required]
    public function setMapper(LinkableMapper $mapper) : void {
        $this->mapper = $mapper;
    }

    /**
     * Define options for the form.
     *
     * Set default, optional, and required options passed to the
     * buildForm() method via the $options parameter.
     */
    public function configureOptions(OptionsResolver $resolver) : void {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
    }
}
