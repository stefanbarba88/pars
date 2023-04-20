<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskLogController extends AbstractController
{
    #[Route('/task/log', name: 'app_task_log')]
    public function index(): Response
    {
        return $this->render('task_log/index.html.twig', [
            'controller_name' => 'TaskLogController',
        ]);
    }
}
