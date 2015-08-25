<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Common\Grav;
use Grav\Common\Page\Collection;
use Grav\Common\Page\Page;
use Grav\Common\Debugger;
use Grav\Common\Taxonomy;
use RocketTheme\Toolbox\Event\Event;

class ArchivesPlugin extends Plugin
{
    /**
     * @var ArchivesPlugin
     */


    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    /**
     * Initialize configuration
     */
    public function onPluginsInitialized()
    {
        if ($this->isAdmin()) {
            $this->active = false;
            return;
        }

        // Dynamically add the needed taxonomy types to the taxonomies config
        $taxonomy_config = array_merge((array)$this->config->get('site.taxonomies'), ['archives_month', 'archives_year']);
        $this->config->set('site.taxonomies', $taxonomy_config);

        $this->enable([
            'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
            'onPageProcessed' => ['onPageProcessed', 0],
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0]
        ]);
    }

    /**
     * Add current directory to twig lookup paths.
     */
    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    /**
     * Add
     *
     * @param Event $event
     */
    public function onPageProcessed(Event $event)
    {
        // Get the page header
        $page = $event['page'];
        $taxonomy = $page->taxonomy();

        // track month taxonomy in "jan_2015" format:
        if (!isset($taxonomy['archives_month'])) {
            $taxonomy['archives_month'] = array(strtolower(date('M_Y', $page->date())));
        }

        // track year taxonomy in "2015" format:
        if (!isset($taxonomy['archives_year'])) {
            $taxonomy['archives_year'] = array(date('Y', $page->date()));
        }

        // set the modified taxonomy back on the page object
        $page->taxonomy($taxonomy);
    }

    /**
     * Set needed variables to display breadcrumbs.
     */
    public function onTwigSiteVariables()
    {
        /** @var Taxonomy $taxonomy_map */
        $taxonomy_map = $this->grav['taxonomy'];
        $pages = $this->grav['pages'];

        // Get current datetime
        $start_date = time();

        $archives = array();

        // get the plugin filters setting
        $filters = (array) $this->config->get('plugins.archives.filters');
        $operator = $this->config->get('plugins.archives.filter_combinator');

        if (count($filters) > 0) {
            $collection = new Collection();
            $collection->append($taxonomy_map->findTaxonomy($filters, $operator)->toArray());

            // reorder the collection based on settings
            $collection = $collection->order($this->config->get('plugins.archives.order.by'), $this->config->get('plugins.archives.order.dir'));
            $date_format = $this->config->get('plugins.archives.date_display_format');

            // loop over new collection of pages that match filters
            foreach ($collection as $page) {
                // update the start date if the page date is older
                $start_date = $page->date() < $start_date ? $page->date() : $start_date;

                $archives[date($date_format, $page->date())][] = $page;
            }
        }

        // slice the array to the limit you want
        $archives = array_slice($archives, 0, intval($this->config->get('plugins.archives.limit')));

        // add the archives_start date to the twig variables
        $this->grav['twig']->twig_vars['archives_show_count'] = $this->config->get('plugins.archives.show_count');
        $this->grav['twig']->twig_vars['archives_data'] = $archives;
    }
}
