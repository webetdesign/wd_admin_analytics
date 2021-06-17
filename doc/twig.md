## Fonctions twig

#### Vues par page

Cette fonction permet de récupérer le nombre de vues par session d'utilisateur sur une page. 

```twig
    view_for_path(analytics_view_id, path, start)
```

- analytics_view_id : site_id, la variable que l'on a définie en globale tout à l'heure
- path : la route à tester ('/my-route') ne pas oublier le '/' devant 
- start (optional) : définir la plage de récupération des données. Par défaut : '1 month ago'
