<?php

namespace Otomaties\SocialMediaPictureGenerator;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 */

class I18n
{
    /**
     * Load the plugin text domain for translation.
     *
     */
    public function loadTextdomain() : void
    {
        load_plugin_textdomain('otomaties-smp', false, dirname(plugin_basename(__FILE__), 2) . '/languages/');
    }
}
