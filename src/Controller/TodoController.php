<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use App\Entity\Todo;
use App\Form\TodoType;

class TodoController extends AbstractController
{
    #[Route('/todo', name: 'app_todo')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $todos = $doctrine->getRepository(Todo::class)->findBy(['user_id' => $this->getUser()->getId()]);

        return $this->render('todo/index.html.twig', [
            'todos' => $todos,
            'msg' => $request->get('msg')
        ]);
    }

    #[Route('/todo/create', name: 'app_todo_create')]
    public function create(Request $request, SluggerInterface $slugger, ManagerRegistry $doctrine) :Response
    {
        $em = $doctrine->getManager();
        $todo = new Todo();
        $form = $this->createForm(TodoType::class, $todo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $photoFile */
            $photoFile = $form->get('photo')->getData();

            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('todo_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $todo->setPhotoSrc($newFilename);
            }

            $todo->setUserId($this->getUser()->getId());
            $em->persist($todo);
            $em->flush();

            return $this->redirectToRoute('app_todo', ['msg' => [
                'status' => 'success',
                'text' => 'Created Successfully!'
            ]]);
        }

        return $this->render('todo/create.html.twig', [
            'form' => $form,
            'error' => []
        ]);
    }

    #[Route('/todo/edit/{id}', name: 'app_todo_edit')]
    public function update(Request $request, int $id, SluggerInterface $slugger, ManagerRegistry $doctrine) :Response
    {
        $em = $doctrine->getManager();
        $fs = new Filesystem();
        $todo = $em->getRepository(Todo::class)->find($id);
        if (!$todo) {
            throw $this->createNotFoundException(
                'No item found for id '.$id
            );
        }
        if ($todo->getUserId() !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException('Access is denied');
        }

        $form = $this->createForm(TodoType::class, $todo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $photoFile */
            $photoFile = $form->get('photo')->getData();

            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('todo_directory'),
                        $newFilename
                    );
                    $fs->remove($this->getParameter('todo_directory').'/'.$todo->getPhotoSrc());
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $todo->setPhotoSrc($newFilename);
            }

            $todo->setUserId($this->getUser()->getId());
            $em->persist($todo);
            $em->flush();

            return $this->redirectToRoute('app_todo', ['msg' => [
                'status' => 'success',
                'text' => 'Updated Successfully!'
            ]]);
        }

        return $this->render('todo/create.html.twig', [
            'form' => $form,
            'error' => []
        ]);
    }

    #[Route('/todo/delete/{todo}', name: 'app_todo_delete')]
    public function delete(Todo $todo, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        if ( $todo->getPhotoSrc() ) {
            $fs = new Filesystem();
            try {
                $fs->remove($this->getParameter('todo_directory').'/'.$todo->getPhotoSrc());
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }
        }
        $em->remove($todo);
        $em->flush();

        return $this->redirectToRoute('app_todo', ['msg' => [
            'status' => 'success',
            'text' => 'Deleted Successfully!'
        ]]);
    }

}
