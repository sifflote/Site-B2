<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UserPasswordType;
use App\Form\UsersType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/utilisateur', name: 'profile_')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'ProfileController',
        ]);
    }

    #[Security("is_granted('ROLE_USER') and user === currentUser")]
    #[Route('/edition/{id}', name: 'user.edit')]
    public function edit(Users $currentUser, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {

        $form = $this->createForm(UsersType::class, $currentUser);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            if ($hasher->isPasswordValid($currentUser, $form->getData()->getPlainPassword())) {
                $user = $form->getData();
                $em->persist($currentUser);
                $em->flush();

                $this->addFlash('success', 'Les informations ont bien été modifiées');

                return $this->redirectToRoute('profile_index');
            } else {
                $this->addFlash('warning', 'Le mot de passe est incorrect.');
            }
        }


        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Security("is_granted('ROLE_USER') and user === currentUser")]
    #[Route('/edition-mot-de-passe/{id}', 'user_edit_password', methods: ['GET', 'POST'])]
    public function editPassword(Users $currentUser, Request $request,UserPasswordHasherInterface $hasher,
    EntityManagerInterface $em) : Response
    {
        $form = $this->createForm(UserPasswordType::class);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            if($hasher->isPasswordValid($currentUser, $form->getData()['plainPassword'])){
                $currentUser->setPassword(
                    $hasher->hashPassword(
                        $currentUser,
                        $form->getData()['newPassword']
                    ));

                $em->persist($currentUser);
                $em->flush();

                $this->addFlash(
                    'success',
                    'Le mot de passe a bien été modifié.'
                );
                return $this->redirectToRoute('profile_index');
            } else {
                $this->addFlash('warning', 'Le mot de passe renseigné est incorrect.');
            }
        }

        return $this->render('user/edit_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
