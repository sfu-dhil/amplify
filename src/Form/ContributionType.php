<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Contribution;
use App\Entity\ContributorRole;
use App\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

/**
 * Contribution form.
 */
class ContributionType extends AbstractType {
    /**
     * Add form fields to $builder.
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        $builder->add('person', Select2EntityType::class, [
            'label' => 'Person',
            'class' => Person::class,
            'remote_route' => 'person_typeahead',
            'allow_clear' => true,
            'attr' => [
                'add_path' => 'person_new',
                'add_label' => 'Add Person',
            ],
            'placeholder' => 'Search for an existing person by name',
        ]);

        $builder->add('contributorRole', Select2EntityType::class, [
            'label' => 'Role',
            'class' => ContributorRole::class,
            'remote_route' => 'contributor_role_typeahead',
            'allow_clear' => true,
            'attr' => [
                'add_path' => 'contributor_role_new',
                'add_label' => 'Add Role',
            ],
            'placeholder' => 'Search for an existing contributor role by name',
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
            'data_class' => Contribution::class,
        ]);
    }
}
