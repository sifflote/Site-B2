<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UserMDPUseFormType;
use App\Form\UserPasswordType;
use App\Form\UsersType;
use App\Repository\UsersRepository;
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
    /**
     *
     * Page d'accueil du profil
     *
     * @param Users $currentUser
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user === currentUser")]
    #[Route('/{id}', name: 'index')]
    public function index(Users $currentUser, Request $request, EntityManagerInterface $em): Response
    {

        $form = $this->createForm(UsersType::class, $currentUser);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $currentUser = $form->getData();
            $em->persist($currentUser);
            $em->flush();

            $this->addFlash('success', 'Les informations ont bien été modifiées');

            return $this->redirectToRoute('profile_index', ['id' => $currentUser->getId()]);

        }
        return $this->render('user/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification de l'espace sécurité
     *
     * @param Users $currentUser
     * @param Request $request
     * @param UserPasswordHasherInterface $hasher
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user === currentUser")]
    #[Route('/security/{id}', 'user.edit.password', methods: ['GET', 'POST'])]
    public function editPassword(Users                  $currentUser, Request $request, UserPasswordHasherInterface $hasher,
                                 EntityManagerInterface $em): Response
    {
        $form = $this->createForm(UserPasswordType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($hasher->isPasswordValid($currentUser, $form->getData()['plainPassword'])) {
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
                return $this->redirectToRoute('profile_index', ['id' => $currentUser->getId()]);
            } else {
                $this->addFlash('warning', 'Le mot de passe renseigné est incorrect.');
            }
        }
        $formUse = $this->createForm(UserMDPUseFormType::class);

        $formUse->handleRequest($request);
        if ($formUse->isSubmitted() && $formUse->isValid()) {
            if (!$currentUser->getMdpUse()) {
                $currentUser->setPassword(
                    $hasher->hashPassword(
                        $currentUser,
                        $formUse->getData()['plainPassword']
                    ));
                $currentUser->setMdpUse(true);
                $em->persist($currentUser);
                $em->flush();

                $this->addFlash(
                    'success',
                    'Le mot de passe a bien été enregistré.'
                );
                return $this->redirectToRoute('profile_index', ['id' => $currentUser->getId()]);
            }
        }

        return $this->render('user/security.html.twig', [
            'form' => $form->createView(),
            'formUse' => $formUse->createView()
        ]);
    }

    /**
     * Page de paramètre B2
     *
     * @param Users $currentUser
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user === currentUser")]
    #[Route('/edition-b2-parametre/{id}', 'user.edit.b2', methods: ['GET', 'POST'])]
    public function editB2Parametres(Users $currentUser): Response
    {
        return $this->render('user/b2.html.twig', [
        ]);
    }

    /**
     *
     * Modification des paramètre b2
     *
     * @param Users $currentUser
     * @param UsersRepository $usersRepository
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user === currentUser")]
    #[Route('/edition-b2-perpage/{id}', 'user.edit.b2.json', methods: ['POST'])]
    public function b2perPage(Users   $currentUser, UsersRepository $usersRepository,
                              Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $utilisateur = $usersRepository->find($user);
        $utilisateur->setB2RejetsPerPage($request->get('RejetPerPage'));
        $em->persist($utilisateur);
        $em->flush();
        $this->addFlash('success', 'Nombre de rejets par page bien modifié.');
        return $this->redirectToRoute('profile_user.edit.b2', ['id' => $utilisateur->getId()]);

    }
}