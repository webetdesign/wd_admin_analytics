## Data :            
You can make different analytics blocks. See the list in the Block crud
    
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
### Configuration of a block
 
To display a analytics block go in the crud and add it.

### General Parameters

To configure colors go in the crud and add it.

   - 'Couleurs' are used for Doughnut Chart you can add as much as you want
   - 'Couleurs pour les comparaisons' are used for comparaison Chart like 'Utlisateurs cette semaine / semaine dernière'. You have to add exactly two.
   - 'Couleur de dégradé' are use for Chart like 'Utilisateurs par heure'
