<?php

namespace Otomaties\SocialMediaPictureGenerator;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @subpackage SocialMediaPictureGenerator/public
 */

class Frontend
{
    /**
     * Initialize the class and set its properties.
     *
     * @param      string    $pluginName       The name of the plugin.
     */
    public function __construct(private string $pluginName)
    {
    }

    public function showNotices($content)
    {
        if (filter_input(INPUT_GET, 'smp_error')) {
            $errorMessage = null;
            switch (filter_input(INPUT_GET, 'smp_error')) {
                case 'nonce':
                    $errorMessage = __('We suspect your are a bot. Please contact us.', 'otomaties-smp');
                    break;
                case 'no_image':
                case 'invalid_image':
                    $errorMessage = __('No valid image was selected. Please select an image.', 'otomaties-smp');
                    break;
                case 'invalid_watermark':
                    $errorMessage = __('No valid watermark was selected. Please contact us.', 'otomaties-smp');
                    break;
                case 'invalid_generated_image':
                case 'no_files':
                    $errorMessage = __('Something went wrong. Please contact us.', 'otomaties-smp');
                    break;
            }
            if ($errorMessage) {
                $errorMessage = sprintf(
                    apply_filters('otomaties_smp_error_wrappper', '<div class="alert alert-danger">%s</div>'),
                    $errorMessage
                );
                $content = $errorMessage . $content;
            }
        }
        return $content;
    }
}
