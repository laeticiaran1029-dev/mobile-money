# Guide de modification du code

Où toucher quand on ajoute une fonctionnalité au projet Mobile Money (CodeIgniter 4 + SQLite).
Complète [ARCHITECTURE.md](ARCHITECTURE.md), qui décrit la logique métier existante.

---

## 1. La chaîne complète

Une requête traverse toujours les mêmes couches, dans cet ordre :

```
URL  →  app/Config/Routes.php      déclaration de la route (get ou post)
        app/Filters/…              contrôle d'accès (espace opérateur)
        app/Controllers/…          lecture du POST, validation, orchestration
        app/Models/…               accès et calculs sur la base
        app/Views/…                affichage HTML
        base.sql                   schéma, si de nouvelles colonnes sont nécessaires
```

**Règle** : on ajoute du bas vers le haut (base → modèle → contrôleur → route → vue),
et on teste dans le sens inverse.

---

## 2. Ordre des étapes pour une nouvelle fonctionnalité

### Étape 0 — Décider si la base doit changer

Si la fonctionnalité a besoin d'une donnée nouvelle → [base.sql](base.sql).

- Une colonne dans une table existante : l'ajouter dans le `CREATE TABLE` concerné.
- Une nouvelle table : la placer **après** ses tables parentes
  (`operateurs`, `operation`, `comptes` d'abord, puis `prefixes`, `commissions`,
  `frais`, `historique_operation`). Sinon → `FOREIGN KEY constraint failed`.
- Rejouer ensuite la base :

```bash
C:/sqlite/sqlite3.exe writable/mobileMoney.db < base.sql
```

Toute colonne ajoutée doit aussi être déclarée dans le `$allowedFields` du modèle,
sinon `insert()` et `update()` **l'ignorent en silence**.

### Étape 1 — Le modèle (`app/Models/`)

Toute requête SQL et tout calcul vivent ici, jamais dans le contrôleur.

Créer `app/Models/XxxModel.php` :

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class XxxModel extends Model
{
    protected $table         = 'xxx';
    protected $primaryKey    = 'idXxx';
    protected $returnType    = 'array';     // tout le projet manipule des tableaux
    protected $allowedFields = ['colonne1', 'colonne2'];

    public function maMethode(int $id): array
    {
        return $this->where('colonne1', $id)->findAll();
    }
}
```

Modèles existants et ce qu'ils couvrent :

| Modèle | Table | À utiliser pour |
|---|---|---|
| [CompteModel.php](app/Models/CompteModel.php) | `comptes` | solde, `parNumero()`, `situation()`, `masseMonetaire()` |
| [PrefixeModel.php](app/Models/PrefixeModel.php) | `prefixes` | `estActif()`, `operateurDe($numeroTel)` |
| [OperateurModel.php](app/Models/OperateurModel.php) | `operateurs` | `tous()` |
| [OperationModel.php](app/Models/OperationModel.php) | `operation` | constantes `DEPOT` = 1, `RETRAIT` = 2, `TRANSFERT` = 3 |
| [FraisModel.php](app/Models/FraisModel.php) | `frais` | `calculer()`, `baremeDe()`, `chevauche()`, `calculerFraisEtCommission()` |
| [CommissionModel.php](app/Models/CommissionModel.php) | `commissions` | `taux()`, `matrice()`, `definir()` |
| [HistoriqueModel.php](app/Models/HistoriqueModel.php) | `historique_operation` | `duCompte()`, `gainsTotal()`, `gainsParOperateur()`, `montantsDusParOperateur()` |

Avant d'écrire une méthode, vérifier qu'elle n'existe pas déjà dans ce tableau.

### Étape 2 — Le contrôleur (`app/Controllers/`)

Le contrôleur lit le POST, valide, appelle les modèles, redirige. Il ne fait pas de SQL.

Côté **client** → ajouter une méthode dans
[OperationController.php](app/Controllers/OperationController.php).
Côté **opérateur** → dans [OperateurController.php](app/Controllers/OperateurController.php).

Ne créer un nouveau contrôleur que pour un domaine vraiment distinct. En PSR-4,
**nom de la classe = nom du fichier**, sinon l'autoloader ne la trouve pas.

Deux méthodes par fonctionnalité client : une qui affiche, une qui traite.

```php
// GET : affichage du formulaire
public function maPage()
{
    return $this->afficher('maPage', 'Mon titre', [
        'bareme' => (new FraisModel())->baremeDe(OperationModel::RETRAIT),
    ]);
}

// POST : traitement
public function effectuerMaPage()
{
    $compte = $this->compteConnecte();      // helper existant
    if (! is_array($compte)) {
        return $compte;                     // redirection si pas connecté
    }

    $montant = (float) $this->request->getPost('montant');

    if ($montant <= 0) {
        return redirect()->back()->with('erreur', 'Montant invalide.');
    }

    // … calculs via les modèles …

    return redirect()->to('maPage')->with('succes', 'Opération effectuée.');
}
```

Helpers privés déjà disponibles dans `OperationController` — les réutiliser :

| Helper | Rôle |
|---|---|
| `compteConnecte()` | Récupère le compte en session, ou renvoie une redirection vers `/` |
| `afficher($vue, $titre, $donnees)` | Rend `client/$vue` avec `compte` et `titre` déjà injectés |
| `enregistrer(...)` | Écrit soldes + ligne d'historique dans une transaction |
| `formater($montant)` | `number_format` avec espaces, pour les messages |

Dans `OperateurController`, les équivalents sont `echec()` et `succes()` : ils
renvoient du JSON si la requête est AJAX, une redirection sinon.

**Toute écriture qui touche plusieurs tables passe par une transaction** :

```php
$db = \Config\Database::connect();
$db->transStart();
// … updates + insert …
$db->transComplete();
```

Sans ça, un plantage entre le débit et le crédit fait disparaître de l'argent.

### Étape 3 — La route (`app/Config/Routes.php`)

Rien n'est accessible tant que la route n'existe pas.

```php
$routes->get('maPage',  'OperationController::maPage');
$routes->post('maPage', 'OperationController::effectuerMaPage');
```

Pour une page **opérateur**, l'ajouter à l'intérieur du groupe déjà filtré
([Routes.php:31](app/Config/Routes.php#L31)) — elle hérite alors de la protection
de session sans code supplémentaire :

```php
$routes->group('operateur', ['filter' => 'operateur'], static function ($routes) {
    $routes->get('maPage', 'OperateurController::maPage');
});
```

CodeIgniter route sur l'URL **et** la méthode HTTP : un formulaire POST vers une
route déclarée en `get` renvoie `Can't find a route for 'POST: …'`.

### Étape 4 — La vue (`app/Views/`)

- `client/` → pages client, `layout/header`
- `operateur/` → pages opérateur, `layout/header_operateur`

Squelette obligatoire — une vue est du **HTML** dans lequel on ouvre PHP
ponctuellement ; un `<?php` en première ligne casse le `<!DOCTYPE>` :

```php
<?= $this->include('layout/header') ?>

<h1><?= esc($titre) ?></h1>

<form method="post" action="<?= site_url('maPage') ?>">
    <?= csrf_field() ?>
    <input type="number" name="montant" class="form-control" required>
    <button class="btn btn-primary">Valider</button>
</form>

<?= $this->include('layout/footer') ?>
```

Points à ne pas oublier :

- `esc()` sur toute valeur affichée.
- `site_url()` pour les routes, `base_url()` pour les fichiers statiques.
- Le header ouvre `<div class="container">`, le footer le ferme.
- Les messages flash `succes` / `erreur` sont déjà affichés par le header : le
  contrôleur n'a qu'à les poser avec `->with(...)`.

### Étape 5 — Le menu

Une page qui n'est liée nulle part est invisible. Ajouter l'entrée dans :

- [layout/header.php](app/Views/layout/header.php) pour le client
- [layout/header_operateur.php](app/Views/layout/header_operateur.php) pour l'opérateur

```php
<li class="nav-item">
    <a class="nav-link" href="<?= site_url('maPage') ?>">Ma page</a>
</li>
```

### Étape 6 — Vérifier

```bash
C:\php82\php.exe spark serve
```

PHP 8.2 minimum : celui de XAMPP est en 8.0 et ne fonctionne pas avec CI4.

Checklist de test : la page s'affiche → le formulaire poste sans 404 → les erreurs
de saisie sont rattrapées → les soldes bougent de la bonne somme → une ligne
apparaît dans l'historique → l'espace opérateur reflète le changement.

---

## 3. Récapitulatif par type d'ajout

| Ce qu'on ajoute | Fichiers à toucher |
|---|---|
| **Nouvelle opération client** (ex. paiement facture) | `base.sql` (ligne dans `operation` + barème `frais`) → `OperationModel` (constante) → `OperationController` (2 méthodes) → `Routes.php` (get + post) → `Views/client/` → `layout/header.php` |
| **Nouvel écran opérateur** (ex. statistiques) | `HistoriqueModel` (méthode d'agrégation) → `OperateurController` (1 méthode) → `Routes.php` (dans le groupe filtré) → `Views/operateur/` → `layout/header_operateur.php` |
| **Nouvelle règle de calcul de frais** | `FraisModel` uniquement, si les colonnes suffisent ; sinon `base.sql` + `$allowedFields` |
| **Nouveau champ sur une table** | `base.sql` → `$allowedFields` du modèle → `insert()`/`update()` du contrôleur → affichage dans la vue |
| **Nouvelle table** | `base.sql` (après ses parents) → nouveau `XxxModel` → contrôleur → route → vue |
| **Nouvelle protection d'accès** | `app/Filters/` (nouveau filtre) → l'enregistrer dans `app/Config/Filters.php` → l'appliquer via `['filter' => 'nom']` dans `Routes.php` |
| **Nouveau style** | `public/assets/css/style.css` — chargé en dernier, il surcharge Bootstrap ; réutiliser les variables `--mm-*` |

---

## 4. Erreurs fréquentes

| Symptôme | Cause | Où corriger |
|---|---|---|
| `Can't find a route for 'POST: x'` | Route déclarée en `get` | `Routes.php` |
| Le champ n'est pas enregistré, sans erreur | Absent de `$allowedFields` | le modèle |
| `ParseError: unexpected "<"` | `<?php` en tête d'une vue | la vue |
| Contrôleur jamais trouvé | Nom de classe ≠ nom de fichier (PSR-4) | le contrôleur |
| `FOREIGN KEY constraint failed` | `INSERT` enfant avant le parent | `base.sql` |
| Redirection permanente vers le login | Pas d'`idCompte` en session | vérifier `compteConnecte()` |
| `no such table: …` | `base.sql` pas rejoué après modification | relancer la commande sqlite3 |
| `Invalid file: xxx.php` | Vue appelée sans son dossier | `view('client/xxx')` |
| L'argent disparaît sur erreur | Écriture multi-tables hors transaction | encadrer par `transStart()` / `transComplete()` |

---

## 5. Conventions du projet

- Tout est nommé en **français** : méthodes, variables, colonnes.
- Les modèles renvoient des **tableaux** (`$returnType = 'array'`), jamais d'objets.
- Aucun SQL dans les contrôleurs ni dans les vues.
- Les messages utilisateur passent par les flashdata `succes` / `erreur`.
- Les montants sont des `float`, formatés à l'affichage seulement (l'ariary n'a pas
  de subdivision : arrondir les commissions, sinon le total débité ne correspond
  plus à ce qui est affiché).
- Un `csrf_field()` dans chaque formulaire POST.
