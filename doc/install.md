## Install the Bundle :
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

## Configure Bundle : 

1° Update composer

2° Add this lines to config/Bundles.php file : 
```php
    ...
    WebEtDesign\AnalyticsBundle\WDAdminAnalyticsBundle::class => ['all' => true],
    MediaFigaro\GoogleAnalyticsApi\GoogleAnalyticsApi::class => ['all' => true],
    ...
```
3° Create File wd_admin_analytics.yaml : 
```yaml
        wd_admin_analytics:
              parameters:
                     view_ids: [000000000]
                     view_names: ['name']
                     map_key: your-map-key
                     
        # 000000000 = profile id that you can find in the analytics URL, p000000000 :
        #https://analytics.google.com/analytics/web/?hl=en&pli=1#management/Settings/a222222222w1111111111p000000000/   
```
 map_key use for Countries Chart "your-key" 
         [Get Api Key](https://developers.google.com/maps/documentation/javascript/get-api-key#step-1-get-an-api-key), 
         [Enable Api Key](https://cloud.google.com/maps-platform/#get-started)
         
#### If you don't specify a mapKey the map block will be rendered as chart bar
         
4° Add routes :
```yaml
[config/routes.yaml]

wd_admin_analytics.data_api:
  resource: "@WDAdminAnalyticsBundle/Resources/config/routing.yaml"
```       

## Configure API : 

   1°  You need a json file to access the API. Follow [this documentation](https://developers.google.com/analytics/devguides/reporting/core/v4/quickstart/service-php)
   
   2 ° When you have your json file. Save it in var dir of the project.
   
   3° Rename the google_apiclient.yaml file to google-analytics-api and replace content by : 
```yaml
    google_analytics_api:
        google_analytics_json_key: "%env(resolve:GOOGLE_ANALYTICS_JSON_KEY)%"
```            
   4° Create ENV variable with the path of your JSON file :
```dotenv        
   GOOGLE_ANALYTICS_JSON_KEY=../var/analytics-259608.json        
```        
   5° In the json file, copy the client_email and add it to the granted users of you analytics account
        
        https://analytics.google.com/analytics/web/#/
            -> Administration
            -> Gestion des Utilisateurs
            -> +
## Enable Styles :    

Execute :

    bin/console assets:install --symlink
    
Add to file sonata_admin.yaml:
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


## Enable twig functions : 

Add to file config/packages/twig_extensions.yaml
````yaml
services:
    [...]
    WDAdminAnalyticsBundle\Twig\ApiTwigExtension: ~
````

Defined site_id as global variable
```yaml
globals:
    [...]
    analytics_view_id: '%env(GOOGLE_ANALYTICS_VIEW_ID)%'
