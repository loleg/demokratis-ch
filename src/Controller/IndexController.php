<?php

namespace App\Controller;

use App\Entity\Newsletter;
use App\Form\ContactType;
use App\Form\NewsletterType;
use App\Repository\ConsultationRepository;
use App\Repository\ContactRequestRepository;
use App\Repository\NewsletterRepository;
use App\Repository\TagRepository;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index', methods: ['GET', 'POST'])]
    public function index(Request $request, NewsletterRepository $newsletterRepository): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_consultation');
        }

        $newsletter = new Newsletter();
        $form = $this->createForm(NewsletterType::class, $newsletter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newsletterRepository->add($newsletter, true);

            $this->addFlash(
                'newsletter',
                'Jetz bisse drin'
            );

            return $this->redirectToRoute('app_index');
        }

        return $this->render('index/index.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/landing', name: 'app_test', methods: ['GET', 'POST'])]
    public function test(
        ConsultationRepository $consultationRepository,
        Request $request,
        NewsletterRepository $newsletterRepository,
        TagRepository $tagRepository,
    ): Response {
        $consultations = array_slice($consultationRepository->findBy(['status' => 'ongoing'], ['startDate' => 'DESC']), 0, 6);

        $newsletter = new Newsletter();
        $form = $this->createForm(NewsletterType::class, $newsletter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newsletterRepository->add($newsletter, true);

            $this->addFlash(
                'newsletter',
                'Jetz bisse drin'
            );

            return $this->redirectToRoute('app_index');
        }

        return $this->render('index/test.html.twig', [
            'consultations' => $consultations,
            'count' => $consultationRepository->count(),
            'ongoing' => $consultationRepository->count('ongoing'),
            'planned' => $consultationRepository->count('planned'),
            'tags' => $tagRepository->findBy(['approved' => true]),
            'form' => $form,
        ]);
    }

    #[Route('/was-ist-eine-vernehmlassung', name: 'app_index_info', methods: ['GET', 'POST'])]
    public function info(
    ): Response {
        return $this->render('index/info.html.twig', [
        ]);
    }

    #[Route('/kontakt', name: 'app_index_contact', methods: ['GET', 'POST'])]
    public function contact(
        Request $request,
        MailerInterface $mailer,
        ContactRequestRepository $contactRequestRepository,
    ): Response {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contactRequestRepository->save($form->getData(), true);

            $email = (new NotificationEmail())
                ->from('noreply@demokratis.ch')
                ->to('team@demokratis.ch')
                ->subject('Demokratis Kontaktanfrage')
                ->markdown(<<<EOF
                    There is a new contact request.
                    EOF
                )
                ->action('See message', 'https://demokratis.ch/admin')
                ->importance(NotificationEmail::IMPORTANCE_HIGH)
            ;

            $this->addFlash('success-contacted', 'Herzlichen Dank für die Nachricht');

            return $this->redirectToRoute('app_index_contact');
        }

        return $this->render('index/contact.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/impressum', name: 'app_index_terms', methods: ['GET'])]
    public function terms(
    ): Response {
        return $this->render('index/terms.html.twig', [
        ]);
    }
}
