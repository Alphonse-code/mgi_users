<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersType;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

#[Route('/users')]
class UsersController extends AbstractController
{

  


    public function transformJsonBody(Request $request): Request
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            return $request;
        }
        $request->request->replace($data);
        return $request;
    }

// API LISTE USERS
    
    #[Route('/list', name: 'app_users_list', methods: ['GET'])]
    public function getUsers(UsersRepository $usersRepository): JsonResponse
    {
        
        $users = $usersRepository->findAll();
        
        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'user_id' => $user->getId(),
                'user_nom' => $user->getNom(),
                'user_prenom' => $user->getPrenom(),
                'user_email' => $user->getEmail(),
            ];
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }



    // API CREAT USERS
    #[Route('/new_user', name: 'app_new_user', methods: ['GET', 'POST'])]
    public function create_user(Request $request, UsersRepository $usersRepository): JsonResponse
    {
    
            $request = $this->transformJsonBody($request);
           
            // On recuper le valeur de chaque champ
            $nom = $request->get('nom');
            $prenom = $request->get('prenom');
            $email = $request->get('email');
           
            $user = new Users();
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setEmail($email);
           
            $usersRepository->save($user, true);
            $array[] = [
                    'success' => true,
                    'code' => 200,
                    'message' => 'user created successfully',
                ];
        return new JsonResponse($array, Response::HTTP_OK);  
    }


    #[Route('/', name: 'app_users_index', methods: ['GET'])]
    public function index(UsersRepository $usersRepository): Response
    {


        return $this->render('users/index.html.twig', [
            'users' => $usersRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_users_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UsersRepository $usersRepository): Response
    {
        $user = new Users();
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $usersRepository->save($user, true);

            return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('users/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

}
