<?php

class Category_Image_Meta_Field {
    public function __construct() {
        add_action('category_add_form_fields', [$this, 'add_meta_field'], 10, 2);
        add_action('category_edit_form_fields', [$this, 'edit_meta_field'], 10, 2);
        add_action('created_category', [$this, 'save_meta_field'], 10, 2);
        add_action('edited_category', [$this, 'save_meta_field'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_media']);
    }

    public function enqueue_media() {
        if (isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'category') {
            wp_enqueue_media();
        }
    }

    public function add_meta_field($taxonomy) {
        ?>
        <div class="form-field term-group">
            <label for="category-image-id">Category Image</label>
            <input type="hidden" id="category-image-id" name="category-image-id" value="">
            <div id="category-image-wrapper"></div>
            <button type="button" class="button button-secondary" id="category-image-upload">Select Image</button>
        </div>
        <script>
        jQuery(document).ready(function($){
            var mediaUploader;
            $('#category-image-upload').click(function(e) {
                e.preventDefault();
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                mediaUploader = wp.media.frames.file_frame = wp.media({
                    title: 'Choose Category Image',
                    button: { text: 'Choose Image' },
                    multiple: false
                });
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#category-image-id').val(attachment.id);
                    $('#category-image-wrapper').html('<img src="'+attachment.url+'" style="max-width:100px;" />');
                });
                mediaUploader.open();
            });
        });
        </script>
        <?php
    }

    public function edit_meta_field($term) {
        $image_id = get_term_meta($term->term_id, '_category_post_image', true);
        $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
        ?>
        <tr class="form-field term-group-wrap">
            <th scope="row"><label for="category-image-id">Category Image</label></th>
            <td>
                <input type="hidden" id="category-image-id" name="category-image-id" value="<?php echo esc_attr($image_id); ?>">
                <div id="category-image-wrapper">
                    <?php if ($image_url) { echo '<img src="'.esc_url($image_url).'" style="max-width:100px;" />'; } ?>
                </div>
                <button type="button" class="button button-secondary" id="category-image-upload">Select Image</button>
            </td>
        </tr>
        <script>
        jQuery(document).ready(function($){
            var mediaUploader;
            $('#category-image-upload').click(function(e) {
                e.preventDefault();
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                mediaUploader = wp.media.frames.file_frame = wp.media({
                    title: 'Choose Category Image',
                    button: { text: 'Choose Image' },
                    multiple: false
                });
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('#category-image-id').val(attachment.id);
                    $('#category-image-wrapper').html('<img src="'+attachment.url+'" style="max-width:100px;" />');
                });
                mediaUploader.open();
            });
        });
        </script>
        <?php
    }

    public function save_meta_field($term_id) {
        if (isset($_POST['category-image-id']) && '' !== $_POST['category-image-id']) {
            update_term_meta($term_id, '_category_post_image', absint($_POST['category-image-id']));
        } else {
            delete_term_meta($term_id, '_category_post_image');
        }
    }
}

new Category_Image_Meta_Field();