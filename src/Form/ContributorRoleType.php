<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Form;

use App\Entity\ContributorRole;
use Nines\UtilBundle\Form\TermType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ContributorRole form.
 */
class ContributorRoleType extends TermType {
    /**
     * Add form fields to $builder.
     */
    public function buildForm(FormBuilderInterface $builder, array $options) : void {
        $builder->add('relatorTerm', TextType::class, [
            'label' => 'MARC Relator Term',
            'required' => false,
            'attr' => [
                'help_block' => 'One of the three letter codes from <a href="https://www.loc.gov/marc/relators/relaterm.html">this list</a>',
            ],
        ]);
        parent::buildForm($builder, $options);
    }

    /**
     * Define options for the form.
     *
     * Set default, optional, and required options passed to the
     * buildForm() method via the $options parameter.
     */
    public function configureOptions(OptionsResolver $resolver) : void {
        $resolver->setDefaults([
            'data_class' => ContributorRole::class,
        ]);
    }
}
