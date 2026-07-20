## Maeva 4386
### Mes taches
#### Création de la table :
- base.sql
  - table compte (utilisateur)
  - operation (depot ,transfert,retrait)
  - frais(intervalle des frais )
  - historique (historique de l'operation)

- accueil (model-view header)
    -creation de la nav barre dans layout 
    -depot
    -retrait
    -transfert
    -historique

- coté opérateur (view-model-controller-route)
   -liste des clients
   -prefixes
   -modification des frais
   -gains
   -comptes clients


## Mihamintsoa 4264
> Cote vue:
- page de login automatique: nom, prenom, numero, bouton "Valider" [OK]

    > Cote authentification:
            - Modele: CompteModele.php, OperationModele.php, PrefixeModele.php

            - Controller: CompteController.php
                - fonction insererUser: 
                    - verification si le numero existe ou pas
                    - prends les parametres en session
                    - Sécurisation des accès : Intégration d'un blocage de sécurité dans la methode globale afficher() d'OperationController. Si un utilisateur tente d'acceder aux URL d'opérations sans etre connecte (idCompte absent de la session), il est immediatement redirige vers la page de garde /

         
    > cote operation client:
        - /effectuer-depot: Le contrôleur récupère le montant soumis via le formulaire de la vue client/depot, l'ajoute au solde actuel, met à jour la table comptes dans SQLite, actualise la session et enregistre l'événement avec l'idOperation = 1

        - /effectuer-retrait. Le système effectue une vérification stricte pour s'assurer que le montant demandé est disponible sur le compte. Si oui, le solde est débité en base, mis à jour en session, et l'opération est consignée avec l'idOperation = 2

        - Implémentation de la route /effectuer-transfert. Une logique de transaction vérifie que le numéro destinataire existe bien en base et qu'il est différent de l'expéditeur. Le script procède ensuite simultanément au débit de l'expéditeur, au crédit du destinataire et insère la liaison dans l'historique avec l'idOperation = 3

        - Historique des transactions : /historique liée à la vue client/historique. Une requête SQL groupée (groupStart / orWhere) extrait toutes les lignes de votre table relationnelle historique_operation où l'utilisateur apparaît soit comme émetteur (idCompte), soit comme bénéficiaire (idCompteDestinataire), puis les classe de la plus récente à la plus ancienne.