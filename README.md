# mobile-money

## Lancer l'application

```
C:\php82\php.exe spark serve
```

PHP 8.2 minimum requis (le PHP de XAMPP est en 8.0, incompatible avec CodeIgniter 4).

## Base de données

SQLite, fichier `writable/mobileMoney.db`, à créer depuis `base.sql` :

```
sqlite3 writable/mobileMoney.db < base.sql
```
### mdp operateur :admin2024

git add .
git commit -m "Mise a jour du projet sur la branche dev"
git push origin dev