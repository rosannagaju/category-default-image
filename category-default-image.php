<?php
/**
 * Plugin Name: Category Default Image
 * Description: Set a default image for Posts.
 * Version: 1.0
 * Author: Roshan Nagaju
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            Roshan Nagaju
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wc-google-map
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-category-image-meta-field.php';

final class CategoryDefaultImage {
	/**
	 * Constructor.
	 */
	public static function init() {
		add_action( 'save_post', [ __CLASS__, 'set_default_image' ], 10, 3 );
		add_action( 'admin_footer-post.php', [ __CLASS__, 'custom_admin_js' ] );
		add_action( 'admin_footer-post-new.php', [ __CLASS__, 'custom_admin_js' ] );
	}

	/**
	 * Set default image for posts based on subcategory.
	 *
	 * @param int     $post_id The post ID.
	 * @param WP_Post $post    The post object.
	 * @param bool    $update  Whether this is an update or not.
	 * 
	 * @return void
	 */
	public static function set_default_image( $post_id, $post, $update ) {
		// Check if this is a revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Check if the post type is 'post'.
		if ( 'post' !== $post->post_type ) {
			return;
		}

		// Get assigned subcategory terms.
		$terms = get_the_terms( $post_id, 'category' );
		if ( ! $terms || is_wp_error( $terms ) ) {
			return;
		}

		// Get the first subcategory term.
		$term = reset( $terms );
		if ( ! $term ) {
			return;
		}

		// If Subcategory set to other, force update the featured image.
		if ( 'uncategorized' !== $term->slug ) {
			return;
		}

		// Get the term ID.
		$term_id = $term->term_id;
		$post_id = $post->ID;

		// Get the term meta for the default image URL.
		$default_image_id = get_term_meta( $term_id, '_category_post_image', true );
		if ( $default_image_id ) {
			set_post_thumbnail( $post_id, $default_image_id );
		}
	}

	/**
	 * Enqueue custom JavaScript for the admin area.
	 * This script will handle the logic of setting the featured image based on the selected category.
	 * 
	 * @return void
	 */
	public static function custom_admin_js() {
		// Check if we are on the post edit screen.
		if ( ! in_array( get_current_screen()->base, [ 'post', 'post-new', 'post-edit' ], true ) ) {
			return;
		}

		// Enqueue the script only if the post type is 'post'.
		if ( get_current_screen()->post_type !== 'post' ) {
			return;
		}

		// Get all category terms.
		$Category_terms = get_terms( [ 
			'taxonomy' => 'category',
			'hide_empty' => false
		] );

		// If there are no terms, return early.
		if ( is_wp_error( $Category_terms ) || empty( $Category_terms ) ) {
			return;
		}

		// Prepare the term image data for JavaScript.
		// This will map term IDs to their default image IDs and URLs.
		$term_image_data = [];
		foreach ( $Category_terms as $s_term ) {
			$term_id = $s_term->term_id;
			// Get the term meta for the default image URL.
			$default_image_id = get_term_meta( $term_id, '_category_post_image', true );
			if ( $default_image_id ) {
				$term_image_data[ (int) $term_id ] = [ 
					'image_id' => (int) $default_image_id,
					'image_src' => wp_get_attachment_image_url( $default_image_id, 'medium' )
				];

			}
		}
		?>
		<script>
			const categoryImageData = <?php echo wp_json_encode( $term_image_data ); ?>;

			(function ($) {
				$('#categorychecklist').on('change', 'input[type="checkbox"]', function (element) {
					$('#categorychecklist input[type="checkbox"]:checked').not(this).prop('checked', false);

					if (!$(this).is(':checked')) {
						return;
					}

					const value = $(this).val();
					if (categoryImageData.hasOwnProperty(value)) {
						$('#_thumbnail_id').val(categoryImageData[value].image_id).trigger('change');
						$('#postimagediv img').attr('src', categoryImageData[value].image_src).removeAttr('srcset').css('display', '');
					} else {
						$('#_thumbnail_id').val('-1').trigger('change');
						$('#postimagediv img').attr('src', '').removeAttr('srcset').css('display', 'none');
					}
				});
			})(jQuery);
		</script>
		<?php
	}
}

// Initialize the plugin.
CategoryDefaultImage::init();
