<?php

namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Description of PersonForm
 *
 * @author Gregory Jarrige
 * @version 0.1 (2016-09-24)
 */
class PersonForm extends \Form\CrudstdForm {
    
    /**
     * Génération des champs du formulaire
     */
    protected function genFields() {
        if ($this->crud_context == 'U' || $this->crud_context == 'C') {
            $this->form        
            ->add('crud', HiddenType::class, array(
                'constraints' => array(new Assert\NotBlank())
            ))
            ->add('id', HiddenType::class, array(
                'constraints' => array(new Assert\NotBlank())
            ))        
            ->add('first_name', TextType::class, array(
                'constraints' => array(new Assert\NotBlank(), 
                    new Assert\Length(array('min' => 2)),
                    new Assert\Length(array('max' => 30))
                ),
                'attr' => $this->default_attrs       
            ))
            ->add('last_name', TextType::class, array(
                'constraints' => array(new Assert\NotBlank(), 
                    new Assert\Length(array('min' => 2)),
                    new Assert\Length(array('max' => 30))
                ),
                'attr' => $this->default_attrs        
            ))
            ->add('email', EmailType::class, array(
                'constraints' => array(
                    new Assert\NotBlank(array('message'=>'not_blank')), 
                    new Assert\Length(array(
                        'min' => 2, 
                        'minMessage'=> "min_length")),
                    new Assert\Length(array(
                        'max' => 60, 
                        'maxMessage'=> "max_length"))
                ),
                'attr' => $this->default_attrs       
            ))
            ->add('gender', ChoiceType::class, array(
                    'choices' => array(
                        'Femme' => 'F',
                        'Homme' => 'M',
                        'Transgenre' => 'T',
                    ),
                    'attr' => $this->default_attrs ,  
                    'constraints' => array(
                        new Assert\NotBlank(
                            array(
                                'message' => 'not_blank'
                            )
                        )
                    )
                )
            )
            ;            
        } else {
            // Si Consultation ou Suppression, affichage
            // des champs verrouillés et sans contrôle
            $this->form        
            ->add('crud', HiddenType::class, array(
            ))
            ->add('id', HiddenType::class, array(
            ))
            ->add('first_name', TextType::class, array(
                'attr' => $this->default_attrs       
            ))     
            ->add('last_name', TextType::class, array(
                'attr' => $this->default_attrs        
            ))
            ->add('email', TextType::class, array(
                'attr' => $this->default_attrs       
            ))
            ->add('gender', TextType::class, array(
                'attr' => $this->default_attrs
            ))
            ;
        }        
    }

}
