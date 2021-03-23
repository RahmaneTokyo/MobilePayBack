<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\AgenceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=AgenceRepository::class)
 * @ApiResource(
 *     normalizationContext={"groups"={"agence:read"}},
 *     attributes={
 *          "security"="is_granted('ROLE_AdminSystem') || is_granted('ROLE_AdminAgent')",
 *          "securityMessage"="Accès refusé !"
 *     },
 *     itemOperations={
 *          "get"={"path"="/admin/agences/{id}"},
 *          "delete"={"path"="/admin/agences/{id}"}
 *     }
 * )
 */
class Agence
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"compte:read","agence:read","utilisateur:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"compte:read","compte:write","agence:read","utilisateur:read"})
     */
    private $nomAgence;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"compte:read","compte:write","agence:read","utilisateur:read"})
     */
    private $address;

    /**
     * @ORM\Column(type="boolean")
     */
    private $archived = 0;

    /**
     * @ORM\OneToOne(targetEntity=Compte::class, mappedBy="agence", cascade={"persist"})
     * @Groups({"utilisateur:read"})
     */
    private $compte;

    /**
     * @ORM\OneToMany(targetEntity=Utilisateur::class, mappedBy="agence", cascade={"persist"})
     * @Groups({"compte:read","compte:write","agence:read"})
     */
    private $utilisateurs;

    /**
     * @ORM\OneToMany(targetEntity=Caissier::class, mappedBy="agence")
     */
    private $caissiers;

    public function __construct()
    {
        $this->utilisateurs = new ArrayCollection();
        $this->caissiers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomAgence(): ?string
    {
        return $this->nomAgence;
    }

    public function setNomAgence(string $nomAgence): self
    {
        $this->nomAgence = $nomAgence;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

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

    public function getCompte(): ?Compte
    {
        return $this->compte;
    }

    public function setCompte(Compte $compte): self
    {
        // set the owning side of the relation if necessary
        if ($compte->getAgence() !== $this) {
            $compte->setAgence($this);
        }

        $this->compte = $compte;

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
            $utilisateur->setAgence($this);
        }

        return $this;
    }

    public function removeUtilisateur(Utilisateur $utilisateur): self
    {
        if ($this->utilisateurs->removeElement($utilisateur)) {
            // set the owning side to null (unless already changed)
            if ($utilisateur->getAgence() === $this) {
                $utilisateur->setAgence(null);
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
            $caissier->setAgence($this);
        }

        return $this;
    }

    public function removeCaissier(Caissier $caissier): self
    {
        if ($this->caissiers->removeElement($caissier)) {
            // set the owning side to null (unless already changed)
            if ($caissier->getAgence() === $this) {
                $caissier->setAgence(null);
            }
        }

        return $this;
    }
}
