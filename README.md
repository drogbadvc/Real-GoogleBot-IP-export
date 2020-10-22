# Real GoogleBot IP export
Ce petit script permet d'extraire d'un fichier de log les adresses IPs de GoogleBot.
Cela vérifie via le whois, s'il s'agit bien de Google.

## Utilisation
Le fichier ***PHP*** se lance en ligne de commande 

```bash
php export.php access.log 10000
```

### Params
Il faut utiliser deux paramètres :

`filename` => le nom du fichier de log
`number lines` => le nombre de lignes à lire dans le log.

#note
Cela extrait tout dans un fichier CSV. 
Après avec excel, vous pouvez filtrer comme bon vous semble. 
