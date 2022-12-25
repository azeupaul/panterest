<?php

namespace App\Controller;

use App\Entity\Pin;
use App\Form\PinType;
use App\Repository\PinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PinsController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(PinRepository $pinRepository): Response
    {
        return $this->render('pins/index.html.twig', [
            'pins' => $pinRepository->findBy(
                [],
                orderBy: ['createdAt' => 'DESC']
            )
        ]);
    }

    #[Route('/pins/create', name: 'app_pins_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $pin = new Pin;

        $form = $this->createForm(PinType::class, $pin);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($pin);
            $em->flush();

            return $this->redirectToRoute('app_pins_create');
        }

        return $this->render('pins/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/pins/{id<[0-9]+>}', name: 'app_pins_show')]
    public function show(Pin $pin): Response
    {
        return $this->render('pins/show.html.twig', compact('pin'));
    }

    #[Route('/pins/{id}/edit', name: 'app_pins_edit', methods: ['GET', 'PUT'])]
    public function edit(Request $request, Pin $pin, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PinType::class, $pin, [
            'method' => 'PUT'
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('app_pins_edit', ['id' => $pin->getId()]);
        }

        return $this->render('pins/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/pins/{id}/delete', name: 'app_pins_delete', methods: ['DELETE'])]
    public function delete(Request $request, Pin $pin, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('pin_delete_' . $pin->getId(), $request->request->get('_csrf_token'))) {
            $em->remove($pin);
            $em->flush();
        }

        return $this->redirectToRoute('app_home');
    }
}
