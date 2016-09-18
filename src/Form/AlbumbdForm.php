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
 * Description of AlbumbdForm
 *
 * @author Gregory Jarrige
 * @version 0.1 (2016-09-18)
 */
class AlbumbdForm extends \Form\CrudstdForm {
    
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
            ->add('album', TextType::class, array(
                'constraints' => array(new Assert\NotBlank(), 
                    new Assert\Length(array('min' => 2)),
                    new Assert\Length(array('max' => 30))
                ),
                'attr' => $this->default_attrs       
            ))
            ->add('auteur', TextType::class, array(
                'constraints' => array(new Assert\NotBlank(), 
                    new Assert\Length(array('min' => 2)),
                    new Assert\Length(array('max' => 30))
                ),
                'attr' => $this->default_attrs        
            ))
            ->add('editeur', TextType::class, array(
                'constraints' => array(new Assert\NotBlank(), 
                    new Assert\Length(array('min' => 2)),
                    new Assert\Length(array('max' => 30))
                ),
                'attr' => $this->default_attrs       
            ));
            // ajout de la classe CSS js-datepicker pour le champ suivant 
            $field_attrs = $this->default_attrs;
            $field_attrs['class'] .= ' js-datepicker';
            $this->form
            ->add('parution', DateType::class, array(
                'constraints' => array(new Assert\NotBlank()),
                'widget' => 'single_text',

                // do not render as type="date", to avoid HTML5 date pickers
                'html5' => false,

                // add a class that can be selected in JavaScript
                'attr' => $field_attrs,
            ))
            ;            
        } else {
            // Si Consultation ou Suppression, affichage
            // des champs verrouillés et sans contrôle
            $this->form        
            ->add('crud', HiddenType::class, array(
            ))
            ->add('id', HiddenType::class, array(
            ))
            ->add('album', TextType::class, array(
                'attr' => $this->default_attrs       
            ))     
            ->add('auteur', TextType::class, array(
                'attr' => $this->default_attrs        
            ))
            ->add('editeur', TextType::class, array(
                'attr' => $this->default_attrs       
            ))
            ->add('parution', TextType::class, array(
                'attr' => $this->default_attrs
            ))
            ;
        }        
    }

}
