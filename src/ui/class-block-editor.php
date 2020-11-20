<?php
/**
 * Duplicate Post class to manage the block editor UI.
 *
 * @package Duplicate_Post
 */

namespace Yoast\WP\Duplicate_Post\UI;

use Yoast\WP\Duplicate_Post\Utils;

/**
 * Represents the Block_Editor class.
 */
class Block_Editor {

	/**
	 * Holds the object to create the action link to duplicate.
	 *
	 * @var Link_Builder
	 */
	protected $link_builder;

	/**
	 * Initializes the class.
	 *
	 * @param Link_Builder $link_builder The link builder.
	 */
	public function __construct( Link_Builder $link_builder ) {
		$this->link_builder = $link_builder;

		$this->register_hooks();
	}

	/**
	 * Adds hooks to integrate with WordPress.
	 *
	 * @return void
	 */
	public function register_hooks() {
		\add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_scripts' ] );
	}

	/**
	 * Enqueues the necessary JavaScript code for the block editor.
	 *
	 * @return void
	 */
	public function enqueue_block_editor_scripts() {
		\wp_enqueue_script(
			'duplicate_post_edit_script',
			\plugins_url( \sprintf( 'js/dist/duplicate-post-edit-%s.js', Utils::flatten_version( DUPLICATE_POST_CURRENT_VERSION ) ), DUPLICATE_POST_FILE ),
			[
				'wp-blocks',
				'wp-element',
				'wp-i18n',
			],
			DUPLICATE_POST_CURRENT_VERSION,
			true
		);

		\wp_localize_script(
			'duplicate_post_edit_script',
			'duplicatePost',
			[
				'new_draft_link'             => $this->get_new_draft_permalink(),
				'rewrite_and_republish_link' => $this->get_rewrite_republish_permalink(),
				'rewriting'                  => ( ! empty( $_REQUEST['rewriting'] ) ) ? 1 : 0,  // phpcs:ignore WordPress.Security.NonceVerification
			]
		);
	}

	/**
	 * Generates a New Draft permalink for the current post.
	 *
	 * @return string The permalink. Returns empty if the post can't be copied.
	 */
	public function get_new_draft_permalink() {
		$post = \get_post();

		if ( Utils::is_rewrite_and_republish_copy( $post ) ) {
			return '';
		}

		return $this->link_builder->build_new_draft_link( $post );
	}

	/**
	 * Generates a Rewrite & Republish permalink for the current post.
	 *
	 * @return string The permalink. Returns empty if the post cannot be copied for Rewrite & Republish.
	 */
	public function get_rewrite_republish_permalink() {
		$post = \get_post();

		if ( $post->post_status !== 'publish' || Utils::is_rewrite_and_republish_copy( $post ) ) {
			return '';
		}

		return $this->link_builder->build_rewrite_and_republish_link( $post );
	}
}
