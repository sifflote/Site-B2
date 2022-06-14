<?php

namespace App\Controller\Admin;

use App\Entity\B2\Observations;
use App\Entity\B2\Uh;
use App\Entity\Users;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Sifflote - Administration')
            ->renderContentMaximized()
            ;
    }


    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Utilisateurs', 'fa-solid fa-users', Users::class);

        yield MenuItem::section('B2', 'fa-solid fa-map');
        yield MenuItem::linkToCrud('Observations', 'fa-solid fa-rectangle-list', Observations::class);
        yield MenuItem::linkToCrud('UH', 'fa-solid fa-list', Uh::class);

    }
}
