<?php
namespace Otomaties\SocialMediaPictureGenerator;

class Shortcodes
{
    public function pictureGenerator() : string
    {
        $sizes = array_filter((array)get_field('smp_image_sizes', 'option'), function ($size) {
            if (!isset($size['overlay']) || !isset($size['overlay']['ID'])) {
                return false;
            }
            $imagesize = wp_get_original_image_path($size['overlay']['ID']);
            return file_exists($imagesize);
        });
        
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
