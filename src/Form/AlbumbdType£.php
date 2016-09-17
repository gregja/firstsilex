<?php

namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

//use Symfony\Component\Form\Extension\Core\Type\FormType;
//use Symfony\Component\Form\Extension\Core\Type\TextType;
//use Symfony\Component\Form\Extension\Core\Type\HiddenType;
//use Symfony\Component\Form\Extension\Core\Type\EmailType;
//use Symfony\Component\Form\Extension\Core\Type\DateType;
//use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
//use Symfony\Component\Form\Extension\Core\Type\ButtonType;
//use Symfony\Component\Form\Extension\Core\Type\ResetType;
//use Symfony\Component\Form\Extension\Core\Type\SubmitType;
//use Symfony\Component\Validator\Constraints as Assert;

class AlbumbdType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
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
        'attr' => array('class'=>'form-control')        
    ))
    ->add('auteur', TextType::class, array(
        'constraints' => array(new Assert\NotBlank(), 
            new Assert\Length(array('min' => 2)),
            new Assert\Length(array('max' => 30))
        ),
        'attr' => array('class'=>'form-control')        
    ))
    ->add('editeur', TextType::class, array(
        'constraints' => array(new Assert\NotBlank(), 
            new Assert\Length(array('min' => 2)),
            new Assert\Length(array('max' => 30))
        ),
        'attr' => array('class'=>'form-control')        
    ))
    ->add('parution', DateType::class, array(
        'constraints' => array(new Assert\NotBlank()),
        'attr' => array('class'=>'form-control'),
        'widget' => 'single_text',

        // do not render as type="date", to avoid HTML5 date pickers
        'html5' => true,

        // add a class that can be selected in JavaScript
//        'attr' => ['class' => 'js-datepicker'],
    ))
    ->add('save', SubmitType::class, array(
        'attr' => array('label' => 'Enregistrer', 'class'=>'btn btn-success'),
    ))
    ->add('reset', ResetType::class, array(
        'attr' => array('label' => 'Effacer', 'class'=>'btn btn-default'),
    ))
    ;
    }

    /**
     * Returns the class name without the "Type" suffix
     * @return type
     */
    public function getName() {
        return 'albumbd';
    }

}
