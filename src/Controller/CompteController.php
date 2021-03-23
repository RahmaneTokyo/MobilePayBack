<?php

namespace App\Controller;

use App\Entity\Depot;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;

class CompteController extends AbstractController
{
    /**
     * @Route("/compte", name="compte")
     */
    public function index(): Response
    {
        return $this->render('compte/index.html.twig', [
            'controller_name' => 'CompteController',
        ]);
    }

    /**
     * @Route("/api/compte/depot", name="retraitTrans", methods={"PUT"})
     * @param Request $request
     * @param EntityManagerInterface $manager
     */
    public function depotCompte(Request $request, EntityManagerInterface $manager, SerializerInterface $serializer) {
        $depot = $request->getContent();
        $caissier = $this->getUser();
        $compte = $caissier->getAgence()->getCompte();

        $depot = $serializer->decode($depot, 'json');
        $depotCaissier = $serializer->denormalize($depot, Depot::class, true);
        $depotCaissier->addCaissier($caissier);
        $depotCaissier->setDateDepot(new \DateTime);
        $depotCaissier->addCompte($compte);

        $compte->setSolde($compte->getSolde() + $depotCaissier->getMontantDepot());

        $manager->persist($depotCaissier);
        $manager->flush();

        return $this->json($depotCaissier);
    }
}
