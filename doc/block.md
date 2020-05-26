## Data :            
You can make different analytics blocks :

   - **browsers** : to display browsers used last week
   - **countries** : to display a map with the location of users
   - **devices** : to display devices used last week
   - **source** :  to display where users are coming from
   - **users** : to display when the users are coming
   - **userWeek** : to display the difference in the number of visitors between this week and the previous
   - **userYear** : to display the difference in the number of visitors between this year and the previous
    
```yaml   
#sonata_admin.yaml
    dashboard:
        blocks:
        ## config block with paramters. For the view selector
            -  class: col-12
               position: top
               roles: [ROLE_ADMIN]
               type:     cms.admin.analytics.config
               settings:
                   colors: ["rgb(195, 236, 255)","rgb(160, 225, 255)", "rgb(114, 210, 255)", "rgb(063, 194, 255)", "rgb(000, 150, 220)", "rgb(000, 150, 174)"]
                   week_colors: ["rgb(160, 225, 255)", "rgb(000, 150, 220)"]
                   year_colors: ["rgb(160, 225, 255)", "rgb(000, 150, 220)"]
                   users_color: 'rgb(000, 123, 255)'
                   map_color: '#0077ae'

        ## Data block for analytics. You can add many of this block
            -  class: col-12
               position: top
               roles: [ROLE_ADMIN]
               type:     cms.admin.analytics.data
               settings:
                   analytics:
                       - devices:
                             size: "col-md-6"
                             icon: "fa-lg fa fa-mobile"
                             start: "2 months ago"
```   
### Configuration of a block
 
To display a analytics block add it in analytics list like 'devices'
    
   - size : default is col-12. You have to use [bootstrap grid class](https://getbootstrap.com/docs/4.0/layout/grid/)
   - start : beginning of the data period. Default is 'first day of january this year'. [Use PHP Relative Formats](https://www.php.net/manual/fr/datetime.formats.relative.php)                       
   - icon : icon before title of the box. [fontawesome v4](https://fontawesome.com/v4.7.0/)
   
You can't configure the beginning of the data period for userYeek and userYear.

### General Parameters

   - colors => Colors for Doughnut Chart, array of rgb colors (nb color = nb result return for devices/sources/browsers) : 
    `["rgb(160, 225, 255)", "rgb(000, 150, 220)""rgb(195, 236, 255)","rgb(160, 225, 255)"]`
   - week_colors => Colors for UserYeek Chart, array 2 of rgb colors. First is for last week data and second for this week data
    
   - year_colors => Colors for UserYear Chart, array 2 of rgb colors. First is for last year data and second for this year data
   
   - users_color => Percent of this color is use for User/Hours Chart, one rgb color 
    `'rgb(000, 123, 255)'`
   
   - map_color => Percent of this color is use for Countries Chart, one Hexa color. 
                  Only this color have to be in hexa format `'#0077ae'`
   
#### The colors must be in rgb format :
   
    rgb(000, 123, 255)
    
   In the case of a value less than 100 you must add 0 in front, like in the example. Do not add spaces or others. 
   
   You can find a color converter at [ColorConverter](https://www.w3schools.com/colors/colors_converter.asp)
