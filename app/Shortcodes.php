<?php
namespace Otomaties\SocialMediaPictureGenerator;

class Shortcodes
{
    public function pictureGenerator() : string
    {
        $sizes = get_field('smp_image_sizes', 'option');
        ob_start();
        if ($sizes && count($sizes) > 0) {
            include_once __DIR__ . '/../views/form.php';
        } else {
            printf('<p>%s</p>', __('We are preparing the generator. Check back soon.', 'otomaties-smp'));
        }
        $content = ob_get_clean();
        return $content ? $content : '';
    }
}
