<?php

namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ContactType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('prenom', 'text', array(
                    'constraints' => array(
                        new Assert\NotNull(
                                array(
                            'message' => 'Veuillez saisir votre prénom.'
                                )
                        ),
                    new Assert\Length(array(
                        'min' => 2,
                        'minMessage' => 'Le titre doit comporter au minimum deux caractères.',
                        'max' => 30,
                        'maxMessage' => 'la longueur du titre ne peut excéder 30 caractères.'
                    )),
                    )
                        )
                )
                ->add('nom', 'text', array(
                    'constraints' => array(
                        new Assert\NotNull(
                                array(
                            'message' => 'Veuillez saisir votre nom.'
                                )
                        ),
                    new Assert\Length(array(
                        'min' => 2,
                        'minMessage' => 'Le titre doit comporter au minimum deux caractères.',
                        'max' => 30,
                        'maxMessage' => 'la longueur du titre ne peut excéder 30 caractères.'
                    )),
                    )
                        )
                )
                ->add('mail', 'email', array(
                    'constraints' => array(
                        new Assert\NotNull(
                                array(
                            'message' => 'Veuillez saisir votre mail.'
                                )
                        ),
                        new Assert\Email(
                                array(
                            'message' => 'Votre mail est incorrect.'
                                )
                        )
                    )
                        )
                )
                ->add('pays', 'choice', array(
                    'choices' => array(
                        'fr' => 'France',
                        'de' => 'Allemagne',
                        'it' => 'Italie',
                        'es' => 'Espagne'
                    ),
                    'empty_value' => 'Choisissez votre pays',
                    'constraints' => array(
                        new Assert\NotNull(
                                array(
                            'message' => 'Veuillez sélectionner votre pays.'
                                )
                        )
                    )
                        )
                )
                ->add('loisirs', 'choice', array(
                    'choices' => array(
                        'php' => 'PHP',
                        'js' => 'JavaScript',
                        'html' => 'HTML',
                        'as' => 'ActionScript'
                    ),
                    'expanded' => 'true',
                    'multiple' => 'true',
                    'constraints' => array(
                        new Assert\Count(
                                array(
                            'min' => '1',
                            'max' => '3',
                            'minMessage' => 'Vous devez sélectionner au minimum un loisir.',
                            'maxMessage' => 'Vous devez sélectionner au maximum trois loisirs.',
                                )
                        )
                    )
                        )
                )
                ->add('question', 'choice', array(
                    'choices' => array(
                        'oui' => 'Oui',
                        'non' => 'Non'
                    ),
                    'expanded' => 'true',
                    'constraints' => array(
                        new Assert\NotNull(
                                array(
                            'message' => 'Veuillez répondre à la question.'
                                )
                        )
                    )
                        )
                )
        ;
    }

    public function getName() {
        return 'contact';
    }

}
