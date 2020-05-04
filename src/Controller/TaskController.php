<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    /**
     * @IsGranted("ROLE_USER")
     *
     * @Route("/task", name="task_list")
     * @param TaskRepository $taskRepository
     * @return Response
     */
    public function listAction(TaskRepository $taskRepository)
    {
        $tasksNotFinish = $taskRepository->findBy(['isDone'=>false, 'author'=>$this->getUser()]);

        //Permet la recuperation des taches anonyme pour l'admin
        if($this->getUser()->getAdminBool()){
            $tasksNotFinishAnonyme = $taskRepository->findBy(['isDone'=>false, 'author'=>null]);
            $tasksNotFinish = array_merge($tasksNotFinish,$tasksNotFinishAnonyme);
        }

        return $this->render('task/list.html.twig',
            [
                'tasks' => $tasksNotFinish,
            ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     *
     * @Route("/task/finish", name="task_finish")
     * @param TaskRepository $taskRepository
     * @return Response
     */
    public function listActionFinish(TaskRepository $taskRepository)
    {
        $tasksFinish = $taskRepository->findBy(['isDone'=>true,'author'=>$this->getUser()]);

        //Permet la recuperation des taches anonyme pour l'admin
        if($this->getUser()->getAdminBool()){
            $tasksFinishAnonyme = $taskRepository->findBy(['isDone'=>false, 'author'=>null]);
            $tasksFinish = array_merge($tasksFinish,$tasksFinishAnonyme);
        }


        return $this->render('task/listeFinish.html.twig',
            [
                'tasks'  => $tasksFinish,
            ]);
    }


    /**
     * @IsGranted("ROLE_USER")
     *
     * @Route("/tasks/create", name="task_create")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request)
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $task->setAuthor($this->getUser());

            $em->persist($task);
            $em->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Security("(is_granted('ROLE_USER') and user === task.getAuthor()) or (is_granted('ROLE_ADMIN') and null === task.getAuthor())")
     * @Route("/tasks/{id}/edit", name="task_edit")
     * @param Task $task
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function editAction(Task $task, Request $request)
    {
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    /**
     * @Security("(is_granted('ROLE_USER') and user === task.getAuthor()) or (is_granted('ROLE_ADMIN') and null === task.getAuthor())")
     *
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     * @param Task $task
     * @return RedirectResponse
     */
    public function toggleTaskAction(Task $task)
    {
        $task->toggle(!$task->isDone());
        $this->getDoctrine()->getManager()->flush();

        if($task->isDone()){
            $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));
        }else{
            $this->addFlash('success', sprintf('La tâche %s est maintenant dans la liste des tâches à faire.', $task->getTitle()));
        }

        return $this->redirectToRoute('task_list');
    }

    /**
     * @Security("(is_granted('ROLE_USER') and user === task.getAuthor()) or (is_granted('ROLE_ADMIN') and null === task.getAuthor())")
     *
     * @Route("/tasks/{id}/delete", name="task_delete")
     * @param Task $task
     * @return RedirectResponse
     */
    public function deleteTaskAction(Task $task)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($task);
        $em->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list');
    }
}
