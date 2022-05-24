## Installation du bundle :
```json
{
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/webetdesign/wd_admin_analytics.git"
        }
    ],
}
```
```json
{
  "require": {
    webetdesign/wd_admin_analytics: "^3"
  }
}
```

## Configuration du bundle : 

1° Mise à jour de composer 
```yaml
    composer update
```

2° Modifier le fichier config/Bundles.php : 
```php
    #...
    WebEtDesign\AnalyticsBundle\WDAdminAnalyticsBundle::class => ['all' => true],
    #...
```
3° Créer un fichier wd_admin_analytics.yaml : 
```yaml
        wd_admin_analytics:
              parameters:
                     view_ids: [000000000]
                     view_names: ['name']
                     map_key: your-map-key
                     google_analytics_json_key: "%kernel.project_dir%/%env(resolve:GOOGLE_ANALYTICS_JSON_KEY)%"
                     
        # 000000000 = profile id that you can find in the analytics URL, p000000000 :
        #https://analytics.google.com/analytics/web/?hl=en&pli=1#management/Settings/a222222222w1111111111p000000000/   
```
 map_key est utilisée pour représenter les pays sous forme de carte "your-key" 
         [Get Api Key](https://developers.google.com/maps/documentation/javascript/get-api-key#step-1-get-an-api-key), 
         [Enable Api Key](https://cloud.google.com/maps-platform/#get-started)
         
#### Si aucune clé d'api Maps n'est ajoutée, le bloc de pays sera rendu sous la forme d'un diagramme en bâtons
         
4° Ajouter les routes :
```yaml
[config/routes.yaml]

wd_admin_analytics.data_api:
  resource: "@WDAdminAnalyticsBundle/Resources/config/routing.yaml"
```       

## Configuration de l'API : 

   1°  Pour configurer l'API il faut un fichier JSON d'authorisation. Suivre [la documentation](https://developers.google.com/analytics/devguides/reporting/core/v4/quickstart/service-php)
   
   2 ° Sauvegarder ce fichier dans le dossier var du projet.
   
```            
   4° Créer une variable d'environnement avec le chemin depuis la racine du projet vers le fichier json :
```dotenv   
   #.env.local 
   GOOGLE_ANALYTICS_JSON_KEY=var/analytics.json        
```   
```dotenv   
   #.env
   GOOGLE_ANALYTICS_JSON_KEY=analytics.json        
```        
   5° Copier l'adresse mail qui se trouve dans le fichier json et l'ajouter aux utilisateurs autorisés sur le compte analytics (droit de lecture)
        
        https://analytics.google.com/analytics/web/#/
            -> Administration
            -> Gestion des Utilisateurs
            -> +
   6° Créer un fichier factice à la racine du projet pour le déploiement circleci:
   ````json
     {
       "type": "service_account",
       "project_id": "null",
       "private_key_id": "",
       "private_key": "",
       "client_email": "",
       "client_id": "",
       "auth_uri": "https://accounts.google.com/o/oauth2/auth",
       "token_uri": "https://oauth2.googleapis.com/token",
       "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
       "client_x509_cert_url": ""
     } 
````  
   7° Modifier le fichier rsync_exclude
   ````text
    var/*.json
````     
## Ajout du css :    

Exécuter la commande :

    bin/console assets:install --symlink
    
modifier le fichier sonata_admin.yaml:
```yaml
    // sonata_admin.yaml
    assets:
        extra_javascripts:
            [...]
            - bundles/wdadminanalytics/admin_analytics.js
        extra_stylesheets:
            [...]
            - bundles/wdadminanalytics/admin_analytics.css
```  


## Ajouter les fonctions twig : 

Modifier le fichier config/packages/twig_extensions.yaml
````yaml
services:
    [...]
    WDAdminAnalyticsBundle\Twig\ApiTwigExtension: ~
````

Définir la variable site_id comme une variable globale
```yaml
globals:
    [...]
    analytics_view_id: '%env(GOOGLE_ANALYTICS_VIEW_ID)%'
```

## Ajouter les cruds : 
```yaml
sonata_admin:
  dashboard:
    groups:
       admin:
          [...]
          items:           
            - cms.admin.analytics.block
            - cms.admin.analytics.configuration
```
