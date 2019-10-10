<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Entity\DefaultProject;
use App\Entity\Project;
use App\Entity\Service;
use App\Service\MiteService;

class DefaultProjectFormType extends AbstractType
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
            ->add('project', EntityType::class,  [
                'class' => Project::class,
                'label' => false,
                'placeholder' => 'Select a project',
                'choices' => $this->getMiteProjects(), // request all available mite projects
                'choice_label' => 'name', 
                'choice_value' => 'id', 
            ])
            ->add('service', ChoiceType::class,  [
                'label' => false,
                'placeholder' => 'Select a service',
                'choices' => $this->getMiteServices(), 
                'choice_label' => 'name', 
                'choice_value' => 'id', 

            ]);
    }

    function getMiteProjects()
    {
        $projectsStdClass = $this->miteService->getMiteProjects();
        $projects = array();
        foreach ($projectsStdClass as $key => $value) {
            $project = new Project();
            $project->setId($value->project->id);
            $project->setName($value->project->name);
            array_push($projects, $project);
        }
        return $projects;
    }

    function getMiteServices()
    {
        $servicesStdClass = $this->miteService->getMiteServices();
        $services = array();
        foreach ($servicesStdClass as $key => $value) {
            $service = new Service();
            $service->setId($value->service->id);
            $service->setName($value->service->name);
            array_push($services, $service);
        }

        return $services;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DefaultProject::class,
        ]);
    }
}
