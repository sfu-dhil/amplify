<?php

declare(strict_types=1);

namespace App\Form;

use App\Config\ContributorRole;
use App\Entity\Contribution;
use App\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

/**
 * Contribution form.
 */
class ContributionType extends AbstractType {
    public function __construct(
        public UrlGeneratorInterface $router,
    ) {}

    /**
     * Add form fields to $builder.
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        $builder->add('person', Select2EntityType::class, [
            'label' => 'Person',
            'required' => true,
            'class' => Person::class,
            'remote_route' => 'person_typeahead',
            'remote_params' => ['podcast_id' => $options['podcast']->getId()],
            'allow_clear' => true,
            'attr' => [
                'add_route' => $this->router->generate('person_new', ['podcast_id' => $options['podcast']->getId()]),
                'add_label' => 'Add New Person',
                'add_modal' => true,
            ],
            'placeholder' => 'Search for an existing person by name',
        ]);

        $builder->add('roles', EnumType::class, [
            'label' => 'Roles',
            'required' => true,
            'multiple' => true,
            'class' => ContributorRole::class,
            'choice_label' => fn (?ContributorRole $role) : string => $role ? $role->label() : '',
            'help' => 'Select one or more roles from the list of <a target="_blank" href="https://www.loc.gov/marc/relators/relaterm.html">MARC relator terms</a>. You can see view the full descriptions of each role <a target="_blank" href="https://www.loc.gov/marc/relators/relaterm.html">here</a>.',
            'help_html' => true,
            'attr' => [
                'class' => 'select2-simple',
                'data-theme' => 'bootstrap-5',
            ],
            'placeholder' => 'Select contributor roles',
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
            'podcast' => null,
        ]);
    }
}
