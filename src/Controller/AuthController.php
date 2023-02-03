<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\SecurityBundle\Security;

use App\Entity\User;

class AuthController extends AbstractController
{
    #[Route('/', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils, Security $security): Response
    {
        // if logged in already
        if ( $security->getUser() ) {
            return $this->redirectToRoute('app_todo');
        }

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    #[Route('/register', name: 'app_register', methods: ['get'])]
    public function register() : Response
    {
        $error = [];

        return $this->render('auth/register.html.twig', [
            'error' => $error
        ]);
    }

    #[Route('/register', name: 'app_do_register', methods: ['post'])]
    public function registerAction(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $ph) : Response
    {
        $em = $doctrine->getManager();
        $error = [];

        // validation
        if (! $request->get('username') || ! $request->get('email') || ! $request->get('password')) {
            $error['messageData'] = 'Required parameters are missing!';
            return $this->render('auth/register.html.twig', [
                'error' => $error
            ]);
        }
        // validate email address; should be unique
        $users = $doctrine->getRepository(User::class)->findBy(['email' => $request->get('email')]);
        if (count($users) > 0) {
            $error['messageData'] = 'Email address should be unique!';
            return $this->render('auth/register.html.twig', [
                'error' => $error
            ]);
        }

        // register user
        $user = new User();
        $user->setUsername($request->get('username'))
            ->setEmail($request->get('email'))
            ->setPassword($ph->hashPassword($user, $request->get('password')));
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('app_login');
    }
}
