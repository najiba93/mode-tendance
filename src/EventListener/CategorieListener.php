<?php

namespace App\EventListener;

use App\Repository\CategorieRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class CategorieListener implements EventSubscriberInterface
{
    private CategorieRepository $categorieRepository;
    private Environment $twig;

    public function __construct(CategorieRepository $categorieRepository, Environment $twig)
    {
        $this->categorieRepository = $categorieRepository;
        $this->twig = $twig;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        // Passer les catégories au template Twig globalement
        $categories = $this->categorieRepository->findAll();
        $this->twig->addGlobal('categories', $categories);
    }
} 