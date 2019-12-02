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
use App\Entity\DailyMiteEntryEntity;
use App\Entity\Project;
use App\Entity\Service;



class DailyMiteEntryFormType extends AbstractType
{

    private $defaultProjectsService;
    private $defaultServicessService;

    public function __construct( MiteService $miteService)
    {
        $this->miteService = $miteService;
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
                'choices' => $this->getMiteProjects(), // request all available mite projects
                'choice_label' => 'name', 
                'choice_value' => 'id', 
            ])
            ->add('service', ChoiceType::class,  [
                'label' => false,
                'placeholder' => 'Select a service',
                'choices' => $this->getMiteServices(), 
                'choice_label' => 'name', 
                'choice_value' => 'id' 
            ])
            ->add('weekdays', ChoiceType::class,  [
                'label' => false,
                'placeholder' => 'Select weekdays',
                'choices'  => [
                    'Monday' => 1,
                    'Tuesday' => 2,
                    'Wednesday' => 3,
                    'Thursday' => 4,
                    'Friday' => 5,
                    'Saturday' => 6,
                    'Sunday' => 0,
                ],
                'expanded' => false,
                'multiple' => true
            ])

            ->add('saveBtn', SubmitType::class);
    }

    function getMiteProjects()
    {
        $projectsStdClass = $this->miteService->getMiteProjects();
        $projects = array();
        foreach ($projects as $key => $value) {
            $project = new Project($value->project->id, $value->project->name);
            array_push($projects, $project);
        }
        return $projects;
    }

    function getMiteServices()
    {
        $servicesStdClass = $this->miteService->getMiteServices();
        $services = array();
        foreach ($servicesStdClass as $key => $value) {
            $service = new Service($value->service->id, $value->service->name);
            array_push($services, $service);
        }

        return $services;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DailyMiteEntryEntity::class,
        ]);
    }

}