<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\AdminAgentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=AdminAgentRepository::class)
 * @ApiResource(
 *     normalizationContext={"groups"={"agent:read"}},
 *     itemOperations={
 *      "get"={"path": "/agent/{id}"}
 *     }
 * )
 */
class AdminAgent extends User
{

}
