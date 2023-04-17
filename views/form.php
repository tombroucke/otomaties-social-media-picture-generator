<form action="<?php echo admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">
    <?php foreach ($sizes as $size) : ?>
        <?php
            $overlayPath = wp_get_original_image_path($size['overlay']['ID']);
            $imagesize = getimagesize($overlayPath);
        ?>
        <div class="<?php echo apply_filters('otomaties_smp_input_wrapper_class', 'mb-3'); ?>">
            <label 
                for="size-<?php echo sanitize_title($size['name']) ?>" 
                class="<?php echo apply_filters('otomaties_smp_form_label_class', 'form-label'); ?>"
            >
                <?php echo $size['name']; ?>
            </label>
            <?php if (isset($size['description']) && $size['description'] !== '') : ?>
                <p><?php echo $size['description']; ?></p>
            <?php endif; ?>
            <input 
                class="<?php echo apply_filters('otomaties_smp_file_input_class', 'form-control'); ?>" 
                type="file" 
                name="image_size[<?php echo sanitize_title($size['name']) ?>]" 
                id="size-<?php echo sanitize_title($size['name']) ?>"
                accept=".jpg, .jpeg, .png"
            >
            <div class="<?php echo apply_filters('otomaties_smp_file_input_instructions_class', 'form-text'); ?>">
                <?php echo $imagesize[0]; ?> x <?php echo $imagesize[1]; ?> px.
            </div>
        </div>
    <?php endforeach; ?>
    <input type="hidden" name="action" value="generate-social-media-pictures" />
    <?php wp_nonce_field('generate_social_media_picture', 'smp_nonce') ?>
    <button type="submit" class="<?php echo apply_filters('otomaties_smp_submit_button_class', 'btn btn-primary'); ?>">
        <?php _e('Generate', 'otomaties-smp') ?>
    </button>
</form>
