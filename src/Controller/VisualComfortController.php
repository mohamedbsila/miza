<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VisualComfortController extends AbstractController
{
    #[Route('/visual-comfort', name: 'app_visual_comfort')]
    public function index(): Response
    {
        return $this->render('visual_comfort/index.html.twig');
    }
} 