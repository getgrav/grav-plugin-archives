# Grav Archives Plugin

`Archives` is a [Grav](http://github.com/getgrav/grav) plugin that automatically appends a `month_year` taxonomy to all pages. It then provides a `partials\archives.html.twig` template which you can include in a blog sidebar, that then is able to create links that will display pages from that month/year.  This is a very handy feature to have for blogs.

# Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `archives`.

You should now have all the plugin files under

	/your/site/grav/user/plugins/archives

# Add Taxonomy

### NOTE: VERY IMPORTANT!

The plugin wil not function properly unless you tell Grav to pay attention to the `month` taxonomy type.  You will need to append this to the `taxonomies` property in your `user/config/site.yaml` file. You should copy over the default value in `system/config/site.yaml` into your user configuration.  It could look something like this:

```
taxonomies: [category,tag,month]
```

>> NOTE: This plugin is a modular component for Grav which requires [Grav](http://github.com/getgrav/grav), the [Error](https://github.com/getgrav/grav-plugin-error) and [Problems](https://github.com/getgrav/grav-plugin-problems) plugins, and a theme to be installed in order to operate.

# Usage

The `archives` plugin comes with some sensible default configuration, that are pretty self explanatory:

# Config Defaults

```
enabled: true
built_in_css: true
date_display_format: 'F Y'
show_count: true
limit: 12
order:
    by: date
    dir: desc
filters:
    category: blog
```

If you need to change any value, then the best process is to copy the [archives.yaml](archives.yaml) file into your `users/config/plugins/` folder (create it if it doesn't exist), and then modify there.  This will override the default settings.

# Template Override

Something you might want to do is to override the look and feel of the archives, and with Grav it is super easy.

Copy the template file [templates/partials/archives.html.twig](templates/partials/archives.html.twig) into the `templates/partials` folder of your custom theme, and that is it.

```
/your/site/grav/user/themes/custom-theme/templates/partials/archives.html.twig
```

You can now edit the override and tweak it however you prefer.
