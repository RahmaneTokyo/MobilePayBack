<?php

namespace App\Controller;

use App\Entity\Compte;
use App\Entity\Comptes;
use App\Entity\Transactions;
use App\Repository\ClientsRepository;
use App\Repository\TransactionsRepository;
use App\Repository\UsersRepository;
use DateTime;
use App\Entity\Client;
use App\Entity\Transaction;
use App\Entity\Utilisateur;
use App\Repository\ClientRepository;
use App\Repository\CompteRepository;
use App\Repository\TransactionRepository;
use App\Repository\UtilisateurRepository;
use App\Service\transactionService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class TransactionController extends AbstractController
{
    /**
     * @Route("/transaction", name="transaction")
     */
    public function index(): Response
    {
        return $this->render('transaction/index.html.twig', [
            'controller_name' => 'TransactionController',
        ]);
    }

    /**
     * @Route(
     *     name="transactionDepot",
     *     path="/api/utilisateur/depot",
     *     methods={"POST"},
     *     defaults={
     *          "__controller"="App\Controller\TransactionController::transactionDepot",
     *          "__api_resource_class"="App\Entity\Transaction::class",
     *          "__api_collection_operation_name"="transactionDepot",
     *     }
     * )
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $manager
     * @param TransactionRepository $transRepo
     * @param UtilisateurRepository $utilisateurRepository
     * @return JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function transactionDepot(Request $request, SerializerInterface $serializer, EntityManagerInterface $manager, TransactionRepository $transRepo, UtilisateurRepository $utilisateurRepository) {

        $depotTransaction = new Transaction();
        $service = new transactionService($transRepo);

        $depotTransactionJSON = $request->getContent();
        $depotTransactionTab = $serializer->decode($depotTransactionJSON, 'json');
        $clientDepot = $serializer->denormalize($depotTransactionTab['clientDepot'], Client::class, true);
        $clientRetrait = $serializer->denormalize($depotTransactionTab['clientRetrait'], Client::class, true);

        $date = new DateTime();
        $date->format('Y-m-d H:i:s');

        //génération du code de transaction, calcule des commission et frais dans le service de transaction.
        $codeTrans = $service->codeTrans($transRepo);
        $fraisDepot = $service->commissionOperateurDepot($depotTransactionTab["montant"]);
        $fraisRetrait = $service->commissionOperateurRetrait($depotTransactionTab["montant"]);
        $fraisEtat = $service->commissionEtat($depotTransactionTab["montant"]);
        $fraisSysteme = $service->commissionTranfert($depotTransactionTab["montant"]);
        $fraisEnvoi = $service->fraisEnvoi($depotTransactionTab["montant"]);

        //Recupération du token pour distinguer le user qui fait le depot.
        $token = substr($request->server->get("HTTP_AUTHORIZATION"), 7);
        $token = explode(".",$token);

        if (isset($token[1])){
            $payload = $token[1];
            $payload = json_decode(base64_decode($payload));

            $userDepot = $utilisateurRepository->findOneBy([
                'id' => $payload->id
            ]);
            $depotTransaction->setUtilisateurDepot($userDepot);
        }

        //on recupére le compte de l'agence du user_agence et le solde de son compte
        $compteDepot = $userDepot->getAgence()->getCompte();
        $soldeCompte = $compteDepot->getSolde();

        //on détermine si le compte a suffisament d'argent pour frais l'opération de dépot
        if ($soldeCompte < 5000){
            return $this->json('Désolé, mais le solde de votre compte est insuffisant pour cette opération.');
        }

        //si oui on ajoute l'argent de l'opération sur son compte et on modifi la date de mise à jour.
        $compteWithNewData = $compteDepot->setSolde(($soldeCompte - $depotTransactionTab['montant']) + $fraisEnvoi);

        //on fait les set()
        $depotTransaction->setMontant($depotTransactionTab["montant"]);
        $depotTransaction->setDateDepot($date);
        $depotTransaction->setCodeTransaction($codeTrans);
        $depotTransaction->setFraisEnvoi($fraisEnvoi);
        $depotTransaction->setFraisRetrait($fraisRetrait);
        $depotTransaction->setFraisEtat($fraisEtat);
        $depotTransaction->setTTC($fraisSysteme);
        $depotTransaction->setCompteDepot($compteWithNewData);
        $depotTransaction->setClientDepot($clientDepot);
        $depotTransaction->setClientRetrait($clientRetrait);

        $manager->persist($depotTransaction);
        $manager->flush();

        return new JsonResponse($depotTransaction->getCodeTransaction(), Response::HTTP_CREATED, []);

    }

    /**
     * @Route("/api/user/transaction/{code}", name="getTransByCode", methods={"GET"})
     */
    public function getTransByCode($code, TransactionRepository $transRepo)
    {

        $transaction = $transRepo->findOneBy([
            "codeTransaction" => $code
        ]);

        if (!$transaction){
            return $this->json(
                ["message" => "Désolé, mais ce code de transaction n'existe pas."],
                Response::HTTP_FORBIDDEN
            );
        }

        return $this->json($transaction);
    }

    /**
     * @Route("/api/utilisateur/retrait", name="depotCompte", methods={"PUT"})
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $manager
     * @param TransactionRepository $transRepo
     * @param UtilisateurRepository $usersRepo
     * @param ClientRepository $clientsRepo
     * @return JsonResponse
     */
    public function retraitTrans(Request $request,SerializerInterface $serializer ,EntityManagerInterface $manager,TransactionRepository $transRepo, UtilisateurRepository $usersRepo, ClientRepository $clientsRepo)
    {

        $retraitTransJson = $request->getContent();
        $retraitTransTab = $serializer->decode($retraitTransJson, 'json');

        $transaction = $transRepo->findOneBy([
            "codeTransaction" => $retraitTransTab["codeTransaction"]
        ]);

        if ($transaction){
            $dateAnnulation = $transaction->getDateAnnulation();
            if ($dateAnnulation != null) {
                return $this->json('Cette transaction a été annulée !');
            }

            $dateRetrait = $transaction->getDateRetrait();
            if ($dateRetrait != null){
                return $this->json("Désolé, mais cette transaction de retrait a déja été faite.");
            }

            //Recupération du token pour distinguer le user qui fait le retrait.
            $user = $this->getUser();

            $montantTransactions = $transaction->getMontant();
            $soldeCompteUserRetrait = $user->getCompte()->getSolde();


            /*if ($soldeCompteUserRetrait < $montantTransactions){
                return $this->json(
                    ["message" => "Désolé, mais votre solde de compte est insuffisant pour faire la transaction de retrait."],
                    Response::HTTP_FORBIDDEN
                );
            }*/

            $user->getAgence()->getCompte()->setSolde($soldeCompteUserRetrait + $montantTransactions + $transaction->getFraisRetrait() );

            $date = new DateTime();
            $date->format('Y-m-d H:m:s');
            $transaction->setDateRetrait($date);
            $transaction->setCompteRetrait($user->getAgence()->getCompte());

            $manager->persist($transaction);
            $manager->flush();
            return $this->json('retrait effectué');
        }else{
            return $this->json('Désolé, mais ce code de transaction n\'existe pas.');
        }


    }

    /**
     * @Route(path="/api/user/frais/{montant}", name="getFraisByMontant", methods={"GET"})
     * @param $montant
     * @param TransactionRepository $transRepo
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getFraisByMontant($montant, TransactionRepository $transRepo)
    {
        $compte = new Compte();
        $service = new transactionService($transRepo);
        $fraisEnvoi = $service->fraisEnvoi($montant);
        return $this->json($fraisEnvoi);
    }

}
