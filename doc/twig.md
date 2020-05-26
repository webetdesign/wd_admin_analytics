## Twig Functions

#### Views of page

If a same user came many times, it will be counted only once

```twig
    view_for_path(analytics_view_id, path, start)
```

- analytics_view_id : site_id, this variable must be defined as a global variable in twig
- path : the path you want data for ('/my-route') don't forget the '/' 
- start (optional) : defined the start of the period. Default '1 month ago'
