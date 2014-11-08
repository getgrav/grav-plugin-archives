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
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
            'onPageProcessed' => ['onPageProcessed', 0],
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0]
        ];
    }

    /**
     * Initialize configuration
     */
    public function onPluginsInitialized()
    {
        if ($this->isAdmin()) {
            $this->active = false;
        }
    }

    /**
     * Add current directory to twig lookup paths.
     */
    public function onTwigTemplatePaths()
    {
        if (!$this->active) {
            return;
        }

        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    /**
     * Add
     *
     * @param Event $event
     */
    public function onPageProcessed(Event $event)
    {
        if (!$this->active) {
            return;
        }

        // Get the page header
        $page = $event['page'];
        $header = $page->header();
        $taxonomy = $page->taxonomy();

        // If there's a date set, let's check the month taxonomy:
        if (!isset($taxonomy['month'])) {
            // none found, let's create one based on date
            $header->taxonomy['month'] = strtolower(date('M_Y', $page->date()));
            $taxonomy['month'] = array($header->taxonomy['month']);

            // set the modified taxonomy back on the page object
            $page->taxonomy($taxonomy);
        }

    }


    /**
     * Set needed variables to display breadcrumbs.
     */
    public function onTwigSiteVariables()
    {
        if (!$this->active) {
            return;
        }

        /** @var Taxonomy $taxonomy_map */
        $taxonomy_map = $this->grav['taxonomy'];
        $pages = $this->grav['pages'];

        // Get current datetime
        $start_date = strtotime('now');

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
