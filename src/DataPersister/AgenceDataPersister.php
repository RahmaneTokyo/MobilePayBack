<?php


namespace App\DataPersister;


use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Compte;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;

class AgenceDataPersister implements ContextAwareDataPersisterInterface
{
    /**
     * @var UtilisateurRepository
     */
    private $utilisateurRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, UtilisateurRepository $utilisateurRepository) {
        $this->entityManager = $entityManager;
        $this->utilisateurRepository = $utilisateurRepository;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Compte;
    }

    public function persist($data, array $context = [])
    {
        $id = $data->getId();
        if ($data->setArchived(false)) {
            $utilisateurs = $this->utilisateurRepository->findBy(['profil' => $id]);
            foreach ($utilisateurs as $utilisateur) {
                $utilisateur->setArchived(false);
                $this->entityManager->persist($utilisateur);
            }
        }
        $this->entityManager->persist($data);
        $this->entityManager->flush();
    }

    public function remove($data, array $context = [])
    {
        $utilisateur = $data->getUtilisateurs();
        foreach ($utilisateur as $item) {
            $item->setArchived(true);
        }
        $this->entityManager->persist($data);
        $this->entityManager->flush();
    }
}