# Mobile Money — logique du projet

Application de mobile money en CodeIgniter 4 + SQLite.
Deux espaces : **client** (opérations sur son compte) et **opérateur** (configuration et supervision).

---

## 1. Démarrer

```bash
C:\php82\php.exe spark serve
```

PHP 8.2 minimum. Le PHP de XAMPP est en 8.0 et **ne fonctionne pas** avec CodeIgniter 4.

Base SQLite dans `writable/mobileMoney.db`, à recréer depuis `base.sql` :

```bash
C:/sqlite/sqlite3.exe writable/mobileMoney.db < base.sql
```

Le `.env` (ligne 42) contient `database.default.database = mobileMoney.db`.
**Sans slash** : CodeIgniter préfixe alors automatiquement par `writable/`. Un chemin
absolu à slashs (`C:/sqlite/x.db`) casse sur Windows, car CI teste la présence de
`\` pour décider si le chemin est absolu ([Connection.php:106](system/Database/SQLite3/Connection.php#L106)).

---

## 2. Schéma de données

```
prefixes                comptes                 operation
├ idPrefixe             ├ idCompte              ├ idOperation
├ valeur      (033…)    ├ numeroTel   (unique)  └ type  (depot|retrait|transfert)
└ statut      (0|1)     ├ solde
                        ├ nom / prenom
                        └ dateCreation
       frais                          historique_operation
       ├ idFrais                      ├ idHistorique
       ├ idOperation  ──┐             ├ idCompte              → comptes
       ├ montantMin     └──────────►  ├ idCompteDestinataire  → comptes (NULL si dépôt/retrait)
       ├ montantMax        operation  ├ idOperation           → operation
       └ frais                        ├ montant
                                      ├ fraisTotal
                                      └ dateTransaction
```

**Identifiants fixes** posés par `base.sql`, repris comme constantes dans
`OperationModel` : dépôt = 1, retrait = 2, transfert = 3.

**Ordre d'insertion obligatoire** dans `base.sql` : `prefixes`, `comptes`, `operation`
d'abord — puis seulement `frais` et `historique_operation`, qui les référencent.
Insérer dans l'autre sens déclenche `FOREIGN KEY constraint failed`.

---

## 3. Règles métier

### 3.1 Authentification client

Login par numéro de téléphone, **sans inscription préalable**.

1. Le numéro doit faire 10 chiffres
2. Ses 3 premiers chiffres doivent exister dans `prefixes` avec `statut = 1`
   → `PrefixeModel::estActif($numeroTel)`
3. Si le compte n'existe pas, il est créé avec un solde de 0
4. `session()->set('idCompte', $id)`

C'est le **contrat de session** : tout le reste de l'application lit `idCompte`.

### 3.2 Calcul des frais

`FraisModel::calculer($idOperation, $montant)` cherche la tranche telle que
`montantMin <= montant <= montantMax`.

Le barème du sujet a des trous (rien entre 1000 et 1001, rien au-dessus de
2 000 000). Décision prise pour éviter un 0 silencieux :

| Cas | Frais appliqués |
|---|---|
| Montant dans une tranche | Frais de la tranche |
| Au-dessus du barème | Frais de la tranche la plus haute |
| Dans un trou entre deux tranches | Frais de la tranche juste en dessous |
| En dessous de la première tranche | 0 |

`FraisModel::chevauche()` empêche l'opérateur de créer deux tranches qui se
recoupent — sans ça, un même montant aurait deux frais possibles.

### 3.3 Les trois opérations

| Opération | Effet sur les soldes | Frais | `idCompteDestinataire` |
|---|---|---|---|
| **Dépôt** | `solde += montant` | 0 | `NULL` |
| **Retrait** | `solde -= (montant + frais)` | oui | `NULL` |
| **Transfert** | émetteur `-= (montant + frais)`, destinataire `+= montant` | oui | id du destinataire |

Le destinataire d'un transfert reçoit le **montant nominal** : les frais sont à la
charge de l'émetteur seul.

**Contrôles à faire avant d'écrire** :
- montant strictement positif
- solde suffisant, frais compris
- transfert : le destinataire existe, et ce n'est pas soi-même

**Transaction obligatoire** — un transfert touche deux comptes et écrit une ligne
d'historique. Sans `$db->transStart()` / `transComplete()`, un plantage entre le
débit et le crédit fait disparaître de l'argent.

### 3.4 Gains de l'opérateur

Somme de `fraisTotal` dans `historique_operation`. Les dépôts étant gratuits,
les gains viennent des retraits et des transferts.

---

## 4. Organisation du code

### Modèles — `app/Models/`

| Modèle | Table | Méthodes clés |
|---|---|---|
| `CompteModel` | `comptes` | `situation()` (avec nb opérations et frais générés), `masseMonetaire()`, `parNumero()` |
| `PrefixeModel` | `prefixes` | `tous()`, `estActif($numeroTel)` |
| `OperationModel` | `operation` | constantes `DEPOT`/`RETRAIT`/`TRANSFERT`, `libelle()` (statique) |
| `FraisModel` | `frais` | `baremeDe()`, `calculer()`, `chevauche()` |
| `HistoriqueModel` | `historique_operation` | `duCompte()`, `gainsParOperation()`, `gainsTotal()`, `dernieres()` |

`$allowedFields` est obligatoire pour les écritures : un champ absent de cette
liste est **silencieusement ignoré** par `insert()` et `update()`.

### Contrôleurs — `app/Controllers/`

| Contrôleur | Rôle |
|---|---|
| `Home` | Affiche le login |
| `Accueil` | Tableau de bord client |
| `OperationController` | Dépôt, retrait, transfert, historique |
| `OperateurController` | Les 4 écrans opérateur |

En PSR-4, **le nom de la classe doit être identique au nom du fichier**, sinon
l'autoloader ne la trouve pas.

Le contrôle de session est factorisé dans `OperationController::afficher()` :
session absente ou compte introuvable → redirection vers `/`.

### Vues — `app/Views/`

```
client/     accueil, login, depot, retrait, transfert, historique
operateur/  home, prefixes, frais, gains, comptes
layout/     header (client), header_operateur, footer
```

Les vues sont appelées avec leur dossier : `view('client/accueil', [...])`.

Structure d'une page :

```php
<?= $this->include('layout/header') ?>
<!-- contenu -->
<?= $this->include('layout/footer') ?>
```

Le `header` ouvre `<div class="container">`, le `footer` le ferme. Passer
`'titre' => '…'` dans `view()` alimente le `<title>`.

Une vue est un fichier **HTML** dans lequel on ouvre PHP ponctuellement. Un `<?php`
en première ligne fait interpréter le `<!DOCTYPE>` comme du code → `ParseError`.

---

## 5. Routes

| Méthode | URL | Action |
|---|---|---|
| GET | `/` | Login |
| GET | `accueil` | Tableau de bord client |
| GET | `depot` `retrait` `transfert` `historique` | Formulaires client |
| GET | `operateur` | Tableau de bord opérateur |
| GET | `operateur/prefixes` | Liste des préfixes |
| POST | `operateur/prefixes/ajouter` | Ajout |
| POST | `operateur/prefixes/basculer/(:num)` | Activer / désactiver |
| GET | `operateur/frais` | Barèmes |
| POST | `operateur/frais/ajouter` | Ajout d'une tranche |
| POST | `operateur/frais/modifier/(:num)` | Modification du montant |
| POST | `operateur/frais/supprimer/(:num)` | Suppression |
| GET | `operateur/gains` | Situation des gains |
| GET | `operateur/comptes` | Situation des comptes |

CodeIgniter route sur **l'URL et la méthode HTTP**. Un formulaire en POST vers une
route déclarée en `get` renvoie un 404 `Can't find a route for 'POST: …'`.

Chaque opération client aura donc **deux** routes : un `get` qui affiche le
formulaire, un `post` qui le traite.

`$indexPage = 'index.php'` dans [App.php:43](app/Config/App.php#L43) : les URLs
contiennent `/index.php/`. Le passer à `''` les raccourcit, à condition que le
serveur pointe sur `public/`.

---

## 6. Interface

### Palette

| Couleur | Variable | Rôle |
|---|---|---|
| `#274DEA` | `--mm-bleu` | Principale — navbar, boutons, liens |
| `#8EB9FC` | `--mm-bleu-clair` | Accent, focus, badge transfert |
| `#EBFFDC` | `--mm-vert-clair` | En-têtes de tableaux, succès, dépôt |
| `#FFF197` | `--mm-jaune` | Avertissements, retrait |
| `#FF513D` | `--mm-rouge` | Erreurs, débits |

`#FF513D` sur fond plein avec du texte blanc donne un contraste de 3,3:1, sous le
seuil lisible de 4,5:1. D'où `--mm-rouge-fonce: #C9301F`, utilisé pour les boutons ;
le rouge d'origine reste en accent (bordures, montants débités).

**Code couleur constant** : dépôt en vert, retrait en jaune, transfert en bleu —
identique sur les tuiles client, les badges et les pastilles.

### Feuilles chargées

```
bootstrap.min.css  →  bootstrap-icons.min.css  →  style.css
```

`style.css` vient en dernier pour pouvoir surcharger Bootstrap.

Bootstrap Icons est installé **en local** (`public/assets/bootstrap-icons/`), pas en
CDN : une démo sans réseau afficherait des carrés vides. Usage :
`<i class="bi bi-nom"></i>`.

### base_url / site_url

- `base_url()` — fichiers statiques (CSS, images)
- `site_url()` — routes de l'application, gère `$indexPage`

---

## 7. État d'avancement

**Fait**
- Schéma de base et données de test
- Tableau de bord client (solde, stats, accès aux opérations, dernières transactions)
- Espace opérateur complet : préfixes, barèmes modifiables, gains, comptes
- Modèles avec toute la logique de calcul
- Charte graphique et icônes

**À faire**
- Traitement POST du login (`Home` affiche la vue, rien ne traite l'envoi)
- Traitement POST des trois opérations — les vues `depot`, `retrait`, `transfert`
  sont des coquilles de 7 lignes
- Page historique client
- Route `logout`
- Réparer `base.sql` (ligne 1 `.mobileMoney` invalide, ordre des `INSERT`)
- Retirer la route `faketest` de [Routes.php](app/Config/Routes.php)
- Tag `v1`

---

## 8. Pièges rencontrés

| Symptôme | Cause |
|---|---|
| Redirection permanente vers le login | Aucun `idCompte` en session |
| `unable to open database file` | Chemin à slashs sur Windows, préfixé par `WRITEPATH` |
| `no such table: comptes` | Base créée mais `base.sql` jamais joué dedans |
| `ParseError: unexpected "<"` | `<?php` en tête d'une vue |
| `Can't find a route for 'POST: x'` | Route déclarée en `get` |
| `Invalid file: accueil.php` | Vue déplacée dans `client/` sans mise à jour du `view()` |
| Contrôleur jamais trouvé | Nom de classe ≠ nom de fichier (PSR-4) |
| `FOREIGN KEY constraint failed` | `INSERT` enfant avant le parent |

---

## 9. Git

Le dépôt n'a **pas de `.gitignore`**. Sont versionnés à tort : `.env`,
`writable/mobileMoney.db`, les logs, les sessions et les fichiers debugbar.

À créer :

```
.env
writable/debugbar/*
writable/logs/*
writable/session/*
writable/cache/*
!writable/**/index.html
writable/*.db
vendor/
```

Puis `git rm -r --cached` sur ces chemins. **Attention** : au prochain `pull`, git
les supprimera aussi chez l'autre développeur — qui devra recréer son `.env` et
rejouer `base.sql`.
