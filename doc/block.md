## Données :     
Pour utiliser les blocs sur la dashboard, il faut ajouter ces deux blocs sans toucher à la configuration       
    
```yaml   
#sonata_admin.yaml
    dashboard:
        blocks:
        ## config block. For the view selector
            -  class: col-12
               position: top
               roles: [ROLE_ADMIN]
               type:     cms.admin.analytics.config
    

        ## Data block for analytics.
            -  class: col-12
               position: top
               roles: [ROLE_ADMIN]
               type:     cms.admin.analytics.data
    
```   
### Configuration des blocs
 
Pour afficher un bloc, il faut aller dans le crud 'Blocs analytics' et l'ajouter

### Paramètres généraux

To configure colors go in the crud and add it.

   - 'Couleurs' est utilisé pour rendre les camemberts, les couleurs seront utilisées dans l'ordre.
   - 'Couleurs pour les comparaisons' est utilisé pour les diagrammes de comparaisons comme 'Utilisateurs cette semaine / semaine dernière'. Il faut ajouter 2 couleurs.
   - 'Couleur de dégradé' est la couleur utilisée pour les dégradés dans les diagrammes comme 'Utilisateurs par heure'
