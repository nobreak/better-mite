<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Entity\DefaultService;
use App\Entity\Service;
use App\Service\MiteService;

class DefaultServiceFormType extends AbstractType
{

    private $miteService;

    public function __construct(MiteService $miteService)
    {
        $this->miteService = $miteService;
    }


// try this https://symfonycasts.com/screencast/symfony-forms/entity-type

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('saveBtn', SubmitType::class)
            ->add('service', EntityType::class,  [
                'class' => Service::class,
                'label' => false,
                'placeholder' => 'Select a service',
                'choices' => $this->getMiteServices(), 
                'choice_label' => 'name', 
                'choice_value' => 'id', 

            ]);
    }


    function getMiteServices()
    {
        $servicesStdClass = $this->miteService->getMiteServices();
        $services = array();
        foreach ($servicesStdClass as $key => $value) {
            $service = new Service($value->service->id,$value->service->name);
            array_push($services, $service);
        }

        return $services;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DefaultService::class,
        ]);
    }
}
