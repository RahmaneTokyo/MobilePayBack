<?php

namespace App\Service;

use App\Repository\TransactionRepository;

class transactionService{

    private $transRepo;

    public function __construct(TransactionRepository $transRepo){
        $this->transRepo = $transRepo;
    }

    public function fraisEnvoi($montant){
        if ($montant <= 5000){
            $fraisEnvoi = 425;
        }else
        if ($montant > 5000 && $montant <= 10000){
            $fraisEnvoi = 850;
        }else
        if ($montant < 10000 && $montant <= 15000){
            $fraisEnvoi = 1270;
        }else
        if ($montant < 15000 && $montant <= 20000){
            $fraisEnvoi = 1695;
        }else
        if ($montant < 20000 && $montant <= 50000){
            $fraisEnvoi = 2500;
        }else
        if ($montant < 50000 && $montant <= 60000){
            $fraisEnvoi = 3000;
        }else
        if ($montant < 60000 && $montant <= 75000){
            $fraisEnvoi = 4000;
        }else
        if ($montant < 75000 && $montant <= 120000){
            $fraisEnvoi = 5000;
        }else
        if ($montant < 120000 && $montant <= 150000){
            $fraisEnvoi = 6000;
        }else
        if ($montant < 150000 && $montant <= 200000){
            $fraisEnvoi = 7000;
        }else
        if ($montant < 200000 && $montant <= 250000){
            $fraisEnvoi = 8000;
        }else
        if ($montant < 250000 && $montant <= 300000){
            $fraisEnvoi = 9000;
        }else
        if ($montant < 300000 && $montant <= 400000){
            $fraisEnvoi = 12000;
        }else
        if ($montant < 400000 && $montant <= 750000){
            $fraisEnvoi = 15000;
        }else
        if ($montant < 75000 && $montant <= 900000){
            $fraisEnvoi = 22000;
        }else
        if ($montant < 900000 && $montant <= 1000000){
            $fraisEnvoi = 25000;
        }else
        if ($montant < 1000000 && $montant <= 1125000){
            $fraisEnvoi = 27000;
        }else
        if ($montant < 1125000 && $montant < 2000000){
            $fraisEnvoi = 30000;
        }else
        if ($montant >= 2000000){
            $fraisEnvoi = ($montant*0.02)/100;
        }

        return $fraisEnvoi;
    }

    public function commissionEtat($montant){
        $commissionEtat = $montant*0.4;
        return $commissionEtat;
    }

    public function commissionTranfert($montant){
        $commissionTranfert = $montant*0.3;
        return $commissionTranfert;
    }

    public function commissionOperateurDepot($montant){
        $commissionOperateurDepot = $montant*0.1;
        return $commissionOperateurDepot;
    }

    public function commissionOperateurRetrait($montant){
        $commissionOperateurRetrait = $montant*0.2;
        return $commissionOperateurRetrait;
    }

    public function codeTrans($transRepo){
        //on génére un code sous le format (xxx-xxx-xxx)
        $newCode = rand(100,999).'-'.rand(100,999).'-'.rand(100,999);
        //on verifie si le code n'existe pas dans la BD.
        $codeExistante = $transRepo->findOneBy(['codeTransaction'=>$newCode]);
        //tant que le code existe, on crée un nouveau
        while ($codeExistante) {
            $newCode = rand(100,999).'-'.rand(100,999).'-'.rand(100,999);
            $codeExistante = $transRepo->findOneBy(['codeTrans'=>$newCode]);
        }
        //on return le nouveau code
        return $newCode;
    }
}