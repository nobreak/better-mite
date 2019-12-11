<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Service\MiteService;
use App\Entity\CalendarMiteEntry;



class CalendarEventFormType extends AbstractType
{

    public function __construct( MiteService $miteService)
    {
        $this->miteService = $miteService;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('toSuggestions', CheckboxType::class, ['mapped' => false, 'label' => false])
            ->add('title', TextType::class, [
                'label' => false, 
                'attr' => ['readonly' => true],
                ])
            ->add('startTime', TextType::class, [
                'label' => false, 
                'attr' => ['readonly' => true],
                ])
            ->add('endTime', TextType::class, [
                'label' => false, 
                'attr' => ['readonly' => true],
                ])
            ->add('duration', TextType::class, [
                'label' => false, 
                'attr' => ['readonly' => true],
                ]);



    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CalendarMiteEntry::class,
        ]);
    }

}