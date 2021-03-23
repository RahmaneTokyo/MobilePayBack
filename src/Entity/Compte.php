<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CompteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=CompteRepository::class)
 * @ApiResource(
 *     normalizationContext={"groups"={"compte:read"}},
 *     denormalizationContext={"groups"={"compte:write"}},
 *     attributes={
 *          "security"="is_granted('ROLE_AdminSystem')",
 *          "securityMessage"="Accès refusé !"
 *     },
 *     collectionOperations={
 *          "get"={"path"="admin/comptes"},
 *          "post"={"path"="admin/comptes"},
 *     },
 *     itemOperations={
 *          "get"={
 *              "path"="admin/comptes/{id}",
 *              "security"="is_granted('ROLE_Utilisateur')",
 *              "security_message"="Accès refusé!"
 *          },
 *          "delete"={"path"="admin/comptes/{id}"},
 *     }
 * )
 */
class Compte
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"compte:read","compte:write","utilisateur:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"compte:read","compte:write"})
     */
    private $numCompte;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"compte:read","compte:write","utilisateur:read"})
     */
    private $solde;

    /**
     * @ORM\Column(type="boolean")
     */
    private $archived = 0;

    /**
     * @ORM\OneToOne(targetEntity=Agence::class, inversedBy="compte", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"compte:read","compte:write"})
     */
    private $agence;

    /**
     * @ORM\OneToMany(targetEntity=Transaction::class, mappedBy="compteDepot")
     */
    private $transactionDepot;

    /**
     * @ORM\OneToMany(targetEntity=Transaction::class, mappedBy="compteRetrait")
     */
    private $transactionRetrait;

    /**
     * @ORM\OneToMany(targetEntity=Utilisateur::class, mappedBy="compte")
     */
    private $utilisateurs;

    /**
     * @ORM\ManyToMany(targetEntity=Caissier::class, mappedBy="comptes", cascade="persist")
     */
    private $caissiers;

    /**
     * @ORM\ManyToMany(targetEntity=Depot::class, mappedBy="comptes")
     */
    private $depots;

    public function __construct()
    {
        $this->transactionDepot = new ArrayCollection();
        $this->transactionRetrait = new ArrayCollection();
        $this->utilisateurs = new ArrayCollection();
        $this->caissiers = new ArrayCollection();
        $this->depots = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumCompte(): ?int
    {
        return $this->numCompte;
    }

    public function setNumCompte(int $numCompte): self
    {
        $this->numCompte = $numCompte;

        return $this;
    }

    public function getSolde(): ?int
    {
        return $this->solde;
    }

    public function setSolde(int $solde): self
    {
        $this->solde = $solde;

        return $this;
    }

    public function getArchived(): ?bool
    {
        return $this->archived;
    }

    public function setArchived(bool $archived): self
    {
        $this->archived = $archived;

        return $this;
    }

    public function getAgence(): ?Agence
    {
        return $this->agence;
    }

    public function setAgence(Agence $agence): self
    {
        $this->agence = $agence;

        return $this;
    }

    /**
     * @return Collection|Transaction[]
     */
    public function getTransactionDepot(): Collection
    {
        return $this->transactionDepot;
    }

    public function addTransactionDepot(Transaction $transactionDepot): self
    {
        if (!$this->transactionDepot->contains($transactionDepot)) {
            $this->transactionDepot[] = $transactionDepot;
            $transactionDepot->setCompteDepot($this);
        }

        return $this;
    }

    public function removeTransactionDepot(Transaction $transactionDepot): self
    {
        if ($this->transactionDepot->removeElement($transactionDepot)) {
            // set the owning side to null (unless already changed)
            if ($transactionDepot->getCompteDepot() === $this) {
                $transactionDepot->setCompteDepot(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Transaction[]
     */
    public function getTransactionRetrait(): Collection
    {
        return $this->transactionRetrait;
    }

    public function addTransactionRetrait(Transaction $transactionRetrait): self
    {
        if (!$this->transactionRetrait->contains($transactionRetrait)) {
            $this->transactionRetrait[] = $transactionRetrait;
            $transactionRetrait->setCompteRetrait($this);
        }

        return $this;
    }

    public function removeTransactionRetrait(Transaction $transactionRetrait): self
    {
        if ($this->transactionRetrait->removeElement($transactionRetrait)) {
            // set the owning side to null (unless already changed)
            if ($transactionRetrait->getCompteRetrait() === $this) {
                $transactionRetrait->setCompteRetrait(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Utilisateur[]
     */
    public function getUtilisateurs(): Collection
    {
        return $this->utilisateurs;
    }

    public function addUtilisateur(Utilisateur $utilisateur): self
    {
        if (!$this->utilisateurs->contains($utilisateur)) {
            $this->utilisateurs[] = $utilisateur;
            $utilisateur->setCompte($this);
        }

        return $this;
    }

    public function removeUtilisateur(Utilisateur $utilisateur): self
    {
        if ($this->utilisateurs->removeElement($utilisateur)) {
            // set the owning side to null (unless already changed)
            if ($utilisateur->getCompte() === $this) {
                $utilisateur->setCompte(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Caissier[]
     */
    public function getCaissiers(): Collection
    {
        return $this->caissiers;
    }

    public function addCaissier(Caissier $caissier): self
    {
        if (!$this->caissiers->contains($caissier)) {
            $this->caissiers[] = $caissier;
            $caissier->addCompte($this);
        }

        return $this;
    }

    public function removeCaissier(Caissier $caissier): self
    {
        if ($this->caissiers->removeElement($caissier)) {
            $caissier->removeCompte($this);
        }

        return $this;
    }

    /**
     * @return Collection|Depot[]
     */
    public function getDepots(): Collection
    {
        return $this->depots;
    }

    public function addDepot(Depot $depot): self
    {
        if (!$this->depots->contains($depot)) {
            $this->depots[] = $depot;
            $depot->addCompte($this);
        }

        return $this;
    }

    public function removeDepot(Depot $depot): self
    {
        if ($this->depots->removeElement($depot)) {
            $depot->removeCompte($this);
        }

        return $this;
    }

}
