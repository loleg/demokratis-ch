<?php

namespace App\Controller;

use App\Entity\Consultation;
use App\Entity\LegalText;
use App\Entity\Statement;
use App\Repository\ConsultationRepository;
use App\Repository\DiscussionRepository;
use App\Repository\DocumentRepository;
use App\Repository\LegalTextRepository;
use App\Repository\MediaRepository;
use App\Repository\OrganisationRepository;
use App\Repository\ParagraphRepository;
use App\Repository\TagRepository;
use App\Repository\UserOrganisationRepository;
use App\Security\OrganisationVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/consultation')]
class ConsultationController extends AbstractController
{
    private Request $request;
    private DocumentRepository $documentRepository;
    private LegalTextRepository $legalTextRepository;
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack, DocumentRepository $documentRepository, LegalTextRepository $legalTextRepository)
    {
        $this->requestStack = $requestStack;
        $this->documentRepository = $documentRepository;
        $this->legalTextRepository = $legalTextRepository;
    }

    #[Route('s/{filter}', name: 'app_consultation', methods: ['GET'])]
    public function index(ConsultationRepository $consultationRepository, OrganisationRepository $organisationRepository, UserOrganisationRepository $userOrganisationRepository, TagRepository $tagRepository, Request $request, EntityManagerInterface $entityManager, string $filter = 'all'): Response
    {
        if (!in_array($filter, ['all', 'ongoing', 'planned', 'done'])) {
            throw new \Exception('Invalid filter');
        }

        $tag = $request->query->get('t');
        $tag = $tagRepository->findOneBy(['slug' => $tag]);

        $organisationSlug = $request->query->get('cp');
        $organisation = $organisationRepository->findOneBy(['slug' => $organisationSlug]);

        if ($organisation !== null) {
            $this->denyAccessUnlessGranted(OrganisationVoter::MEMBER, $organisation);
        }

        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $consultationRepository->getPaginator($offset, $filter, $tag, $organisation);
        $steps = ConsultationRepository::PAGINATOR_PER_PAGE;

        $counts = $consultationRepository->countByStatus();

        return $this->render('consultation/index.html.twig', [
            'consultations' => $paginator,
            'tags' => $tagRepository->findBy(['approved' => true]),
            'currentTag' => $tag,
            'currentOrganisation' => $organisation,
            'organisations' => $this->getUser() ? $organisationRepository->getOrganisationByUser($this->getUser()->getId()) : null,
            'filter' => $filter,
            'ongoingCount' => ($counts['ongoing'] ?? 0),
            'plannedCount' => ($counts['planned'] ?? 0),
            'doneCount' => (($counts['done'] ?? 0) + ($counts['pending_report'] ?? 0) + ($counts['pending_statements_report'] ?? 0)),
            // Paginator
            'offset' => $offset,
            'steps' => $steps,
        ]);
    }

    #[Route('/{slug}/import', name: 'app_consultation_import_paragraphs', methods: ['GET'])]
    public function import(Consultation $consultation, DocumentRepository $documentRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('', $consultation);

        if (count($documentRepository->findBy(['consultation' => $consultation, 'type' => 'proposal', 'imported' => 'fetched'])) === 0) {
            return $this->redirectToRoute('app_consultation_show_statements', ['slug' => $consultation->getSlug()]);
        }

        $proposals = $documentRepository->findBy(['consultation' => $consultation, 'type' => 'proposal']);

        return $this->render('consultation/import.html.twig', [
            'consultation' => $consultation,
            'proposals' => $proposals,
            'documents' => $documentRepository->findBy(['consultation' => $consultation, 'type' => 'document']),
        ]);
    }

    #[Route('/{slug}/legal', name: 'app_consultation_show_legal', methods: ['GET'])]
    public function showLegal(Consultation $consultation, ParagraphRepository $paragraphRepository): Response
    {
        $this->denyAccessUnlessGranted('', $consultation);

        // Import the legal text if not done, yet
        if (count($this->documentRepository->findBy(['consultation' => $consultation, 'type' => 'proposal', 'imported' => 'fetched'])) > 0) {
            return $this->redirectToRoute('app_consultation_import_paragraphs', ['slug' => $consultation->getSlug()]);
        }

        $legalText = $this->getLegalTexts($consultation);

        return $this->render('consultation/legal.html.twig', [
            'consultation' => $consultation,
            'legalText' => $legalText,
            'paragraphs' => $paragraphRepository->findBy(['legalText' => $legalText], ['position' => 'ASC']),
            'legalTexts' => $consultation->getLegalTexts(),
        ]);
    }

    #[Route('/{slug}/documents', name: 'app_consultation_show_documents', methods: ['GET'])]
    public function showDocuments(Consultation $consultation, DocumentRepository $documentRepository, LegalTextRepository $legalTextRepository, Request $request, Statement $statement = null): Response
    {
        $this->denyAccessUnlessGranted('', $consultation);

        // Import the legal text if not done, yet
        if (count($this->documentRepository->findBy(['consultation' => $consultation, 'type' => 'proposal', 'imported' => 'fetched'])) > 0) {
            return $this->redirectToRoute('app_consultation_import_paragraphs', ['slug' => $consultation->getSlug()]);
        }

        $proposals = $documentRepository->findBy(['consultation' => $consultation, 'type' => 'proposal']);

        return $this->render('consultation/documents.html.twig', [
            'consultation' => $consultation,
            'proposals' => $proposals,
            'documents' => $documentRepository->findBy(['consultation' => $consultation, 'type' => 'document']),
        ]);
    }

    #[Route('/{slug}/discussion', name: 'app_consultation_index_discussion', methods: ['GET'])]
    public function indexDiscussion(Consultation $consultation, DiscussionRepository $discussionRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('', $consultation);

        // Import the legal text if not done, yet
        if (count($this->documentRepository->findBy(['consultation' => $consultation, 'type' => 'proposal', 'imported' => 'fetched'])) > 0) {
            return $this->redirectToRoute('app_consultation_import_paragraphs', ['slug' => $consultation->getSlug()]);
        }

        $discussions = $discussionRepository->findBy(['consultation' => $consultation]);

        return $this->render('consultation/discussions.html.twig', [
            'consultation' => $consultation,
            'discussions' => $discussions,
        ]);
    }

    #[Route('/{slug}/media', name: 'app_consultation_index_media', methods: ['GET'])]
    public function indexMedia(Consultation $consultation, MediaRepository $mediaRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('', $consultation);

        // Import the legal text if not done, yet
        if (count($this->documentRepository->findBy(['consultation' => $consultation, 'type' => 'proposal', 'imported' => 'fetched'])) > 0) {
            return $this->redirectToRoute('app_consultation_import_paragraphs', ['slug' => $consultation->getSlug()]);
        }

        $media = $mediaRepository->findBy(['consultation' => $consultation]);

        return $this->render('consultation/media.html.twig', [
            'consultation' => $consultation,
            'media' => $media,
        ]);
    }

    #[Route('/{slug}', name: 'app_consultation_show_statements', methods: ['GET'])]
    public function showStatements(Consultation $consultation): Response
    {
        $this->denyAccessUnlessGranted('show', $consultation);

        // Import the legal text if not done, yet
        if (count($this->documentRepository->findBy(['consultation' => $consultation, 'type' => 'proposal', 'imported' => 'fetched'])) > 0) {
            return $this->redirectToRoute('app_consultation_import_paragraphs', ['slug' => $consultation->getSlug()]);
        }

        if ($consultation->getSingleStatement()) {
            return $this->redirectToRoute('app_statement_show', ['uuid' => $consultation->getSingleStatement()->getUuid()]);
        }

        return $this->render('consultation/statements.html.twig', [
            'consultation' => $consultation,
            'statements' => $consultation->getStatements(),
            'externalStatements' => $consultation->getExternalStatements(),
        ]);
    }

    public function getLegalTexts(Consultation $consultation): LegalText|null
    {
        $lt = $this->requestStack->getCurrentRequest()->query->get('lt');

        $legalText = $this->legalTextRepository->findOneBy(['uuid' => $lt]);

        if (!$legalText) {
            $legalText = $this->legalTextRepository->findOneBy(['consultation' => $consultation]);
        }

        return $legalText;
    }
}
