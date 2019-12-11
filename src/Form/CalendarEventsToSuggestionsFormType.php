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
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use App\Form\CalendarEventFormType;
use App\Service\MiteService;
use App\Entity\DailyMiteEntryEntity;
use App\Entity\Project;
use App\Entity\Service;
use App\Entity\CalendarSuggestionMiteEntries;



class CalendarEventsToSuggestionsFormType extends AbstractType
{

    public function __construct( MiteService $miteService)
    {
        $this->miteService = $miteService;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('events', CollectionType::class, [
                 'entry_type' => CalendarEventFormType::class,
                 'entry_options' => [
                       'label' => false 
            //         'attr' => ['class' => 'email-box'],
                     ],
                 ])
            ->add('project', EntityType::class,  [
                'class' => Project::class,
                'mapped' => false,
                'label' => false,
                'placeholder' => 'Select a project',
                'choices' => $this->getMiteProjects(), // request all available mite projects
                'choice_label' => 'name', 
                'choice_value' => 'id', 
            ])
            ->add('service', ChoiceType::class,  [
                'mapped' => false,
                'label' => false,
                'placeholder' => 'Select a service',
                'choices' => $this->getMiteServices(), 
                'choice_label' => 'name', 
                'choice_value' => 'id' 
            ])
            ->add('date', HiddenType::class, [
                'mapped' => false
            ] )
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
            'data_class' => CalendarSuggestionMiteEntries::class,
        ]);
    }

}