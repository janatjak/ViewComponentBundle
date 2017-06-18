# ViewComponentBundle
View Components Bundle for Symfony 3.3

# Installation
composer require starychfojtu/viewcomponents

# Configuration

```
view_component:
        component_dirs: ['AppBundle/Component', 'AppBundle/SpecialComponent'] #results in '/src/AppBundle/Component', '/src/AppBundle/Component/specialComponent'
        # Specify directories where the bundle should search for components from /src
        template_dirs: ['components', 'specialComponents'] #results in '/templates/components', '/templates/specialComponents'
        # Specify directories where the bundle should search for templates from /templates
```

# Usage

First specify your view component by creating a class in configured directories
and implement ViewComponentInterface. The render method returns associative array of
objects that are passed to the view.

YOU HAVE TO NAME YOUR COMPONENT IN THIS WAY : YourSpecialNameViewComponent

```
# src/AppBundle/SpecialComponent/MenuViewComponent

<?php

namespace AppBundle\SpecialComponent;

use ViewComponent\ViewComponentInterface;

class MenuViewComponent implements ViewComponentInterface
{
    public function render(): array
    {
        return [
            'links' => ['home','about','contact']
        ];
    }
}

```

Then add your template to one of configured directories.

```
# templates/specialComponent/Menu.html.twig

{% for link in links %}
    {{ link }}
{% endfor %}
```

And finally render your component in the view:

```
{{ component('Menu') }}
```

## Custom template name

If you want to specify another template name in view component, just add
a key with template like this:

```
# src/AppBundle/SpecialComponent/MenuViewComponent

<?php

namespace AppBundle\SpecialComponent;

use ViewComponent\ViewComponentInterface;

class MenuViewComponent implements ViewComponentInterface
{
    public function render(): array
    {
        return [
            'links' => ['home','about','contact'],
            'template' => 'AnotherMenu'
        ];
    }
}

```