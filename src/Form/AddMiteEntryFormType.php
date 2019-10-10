<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Service\MiteService;
use App\Service\DefaultProjectsService;
use App\Service\DefaultServicesService;
use App\Entity\MiteEntry;
use App\Entity\Project;
use App\Entity\Service;



class AddMiteEntryFormType extends AbstractType
{

    private $defaultProjectsService;
    private $defaultServicessService;

    public function __construct( DefaultProjectsService $defaultProjectsService, DefaultServicesService $defaultServicesService)
    {
        $this->defaultProjectsService = $defaultProjectsService;
        $this->defaultServicesService = $defaultServicesService;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', TextType::class, [
                'label' => false
                ])
            ->add('minutes', IntegerType::class, [
                'label' => false
                ])
            ->add('project', EntityType::class,  [
                'class' => Project::class,
                'label' => false,
                'placeholder' => 'Select a project',
                'choices' => $this->getProjects(), // request all available mite projects
                'choice_label' => 'name', 
                'choice_value' => 'id', 
            ])
            ->add('service', ChoiceType::class,  [
                'label' => false,
                'placeholder' => 'Select a service',
                'choices' => $this->getServices(), 
                'choice_label' => 'name', 
                'choice_value' => 'id', 

            ])
            ->add('saveBtn', SubmitType::class)
            ->add('date', HiddenType::class );
            
    }

    function getProjects()
    {
        $projectsArrStdClass = $this->defaultProjectsService->readDefaultProjects();
        $projects = array();
        foreach ($projectsArrStdClass as $key => $value) {
            $project = new Project();
            $project->setId($value->id);
            $project->setName($value->name);
            array_push($projects, $project);
        }
        return $projects;

    }


    function getServices()
    {
        $servicesArrStdClass = $this->defaultServicesService->readDefaultServices();
        $services = array();
        foreach ($servicesArrStdClass as $key => $value) {
            $service = new Service();
            $service->setId($value->id);
            $service->setName($value->name);
            array_push($services, $service);
        }
        return $services;

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MiteEntry::class,
        ]);
    }

}