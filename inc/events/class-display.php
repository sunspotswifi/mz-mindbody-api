<?php
namespace MZ_Mindbody\Inc\Events;

use MZ_Mindbody as NS;
use MZ_Mindbody\Inc\Core as Core;
use MZ_Mindbody\Inc\Common as Common;
use MZ_Mindbody\Inc\Common\Interfaces as Interfaces;

class Display extends Interfaces\ShortCode_Script_Loader
{
    /**
     * If shortcode script has been enqueued.
     *
     * @since    2.4.7
     * @access   public
     *
     * @used in handleShortcode, addScript
     * @var      boolean $addedAlready True if shorcdoe scripts have been enqueued.
     */
    static $addedAlready = false;

    /**
     * Shortcode attributes.
     *
     * @since    2.4.7
     * @access   public
     *
     * @used in handleShortcode, localizeScript, display_schedule
     * @var      array $atts Shortcode attributes function called with.
     */
    public $atts;

    /**
     * Events object.
     *
     * @since    2.4.7
     * @access   public
     *
     * @used in handleShortcode, get_events_modal
     * @var      object $events_object The class that retrieves the MBO events.
     */
    public $events_object;

    /**
     * Data to send to template
     *
     * @since    2.4.7
     * @access   public
     *
     * @used in handleShortcode, display_schedule
     * @var      @array    $data    array to send template.
     */
    public $template_data;

    /**
     * Event Location
     *
     * @since 1.0.0
     *
     * For backwards compatibility
     *
     * @access public
     * @var $location array of locations to display events for
     */
    public $location;

    /**
     * Event Locations
     *
     * @since 2.0.0 (estimate)
     *
     * @access public
     * @var $locations array of locations to display events for
     */
    public $locations;

    /**
     * Event List Only View
     *
     * @since 2.0.0 (estimate)
     *
     * @access public
     * @var $list_only boolean True indicates to just display a list of event Name, Date and Times
     */
    public $list_only;

    /**
     * Event Count
     *
     * @since 2.0.0 (estimate)
     *
     * @access public
     * @var $event_count int number of events returned per request to MBO
     */
    public $event_count;

    /**
     * Event Account
     *
     * @since 2.0.0 (estimate)
     *
     * @access public
     * @var $account int The MBO account to retrieve and display events from. Default in Options, overridden in shortcode atts
     */
    public $account;

    /**
     * Event Single Week DIsplay
     *
     * @since 2.0.0 (estimate)
     *
     * @access public
     * @var $week_only boolean True indicates to only display a weeks worth of events, starting with current day
     */
    public $week_only;

    /**
     * Event Locations
     *
     * @since 2.0.0 (estimate)
     *
     * @access public
     * @var $locations array of locations to display events for
     */
    public $number_of_events;

    /**
     *
     *
     * @since    2.4.7
     * @access   public
     *
     * @used in handleShortcode, display_events
     * @var      @array
     */
    public $clientID;

    public function handleShortcode($atts, $content = null)
    {

        $this->atts = shortcode_atts( array(
            'location' => '1',
            'locations' => '',
            'list' => 0,
            'event_count' => '0',
            'account' => '0',
            'week-only' => 0,
            'offset' => 0
        ), $atts );

        /*
         * This is for backwards compatibility for previous to using an array to hold one or more locations.
        */
        if (($this->locations == '') || !isset($this->locations)) {
            if ($this->locations == '') {
                $this->locations = array('1');
            }else{
                $this->locations = array($this->locations);
            }
        }else{
            $this->locations = explode(', ', $atts['locations']);
        }

        ob_start();

        $template_loader = new Core\Template_Loader();

        $this->events_object = new Retrieve_Events($this->atts);

        // Call the API and if fails, return error message.
        if (!$response = $this->events_object->get_mbo_results()) return "<div>" . __("Mindbody plugin settings error.", 'mz-mindbody-api') . "</div>";
        // Add Style with script adder
        self::addScript();

        $events = ($response['GetClassesResult']['ResultCount'] >= 1) ? $response['GetClassesResult']['Classes']['Class'] : __('No Events in current cycle', 'mz-mindbody-api');

        $events = $this->events_object->sort_events_by_time();

        $this->template_data = array(
            'atts' => $this->atts,
            'events' => $events
        );

        $template_loader->set_template_data($this->template_data);
        $template_loader->get_template_part('event_list');

        return ob_get_clean();
    }

    public function addScript()
    {
        if (!self::$addedAlready) {
            self::$addedAlready = true;

            wp_register_style('mz_mindbody_style', NS\PLUGIN_NAME_URL . 'dist/styles/main.css');
            wp_enqueue_style('mz_mindbody_style');

            wp_register_script('mz_mbo_bootstrap_script', NS\PLUGIN_NAME_URL . 'dist/scripts/main.js', array('jquery'), 1.0, true);
            wp_enqueue_script('mz_mbo_bootstrap_script');

        }
    }


}

?>
