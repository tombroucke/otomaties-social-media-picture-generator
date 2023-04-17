<?php

namespace Otomaties\SocialMediaPictureGenerator;

use StoutLogic\AcfBuilder\FieldsBuilder;

/**
 * Add ACF options pages
 */
class OptionsPage
{

    /**
     * Create pages
     *
     * @return void
     */
    public function addOptionsPage() : void
    {
        acf_add_options_page(
            [
                'page_title'    => __('Social Media Picture', 'otomaties-smp'),
                'menu_title'    => __('Social Media Picture', 'otomaties-smp'),
                'menu_slug'     => 'otomaties-smp-settings',
                'capability'    => 'edit_posts',
                'redirect'      => false,
            ]
        );
    }

    /**
     * Add options fields
     *
     * @return void
     */
    public function addOptionsFields() : void
    {
        $projectnameSettings = new FieldsBuilder('otomaties-smp-settings', [
            'title' => __('Social Media Picture Settings', 'otomaties-smp'),
        ]);
        $projectnameSettings
            ->addText('archive_filename', [
                'label' => __('Archive filename', 'otomaties-smp'),
                'instructions' => __('Filename of the archive. Without the file extension. Defaults to \'images\'', 'otomaties-smp'), // phpcs:ignore Generic.Files.LineLength.TooLong
                'default_value' => __('images', 'otomaties-smp'),
                'required' => true,
            ])
            ->addRepeater('smp_image_sizes', [
                'label' => __('Image sizes', 'otomaties-smp'),
                'instructions' => __('Add image sizes', 'otomaties-smp'),
                'button_label' => __('Add image size', 'otomaties-smp'),
            ])
                ->addText('name', [
                    'label' => __('Name', 'otomaties-smp'),
                    'instructions' => __('Name of the image size', 'otomaties-smp'),
                    'required' => true,
                ])
                ->addTextarea('description', [
                    'label' => __('Description', 'otomaties-smp'),
                    'instructions' => __('Description of the image size', 'otomaties-smp'),
                ])
                ->addImage('overlay', [
                    'label' => __('Overlay', 'otomaties-smp'),
                    'instructions' => __('Overlay image', 'otomaties-smp'),
                    'required' => true,
                    'return_format' => 'array',
                    'preview_size' => 'thumbnail',
                    'library' => 'all',
                ])
            ->endRepeater()
            ->setLocation('options_page', '==', 'otomaties-smp-settings');
        acf_add_local_field_group($projectnameSettings->build());
    }
}
