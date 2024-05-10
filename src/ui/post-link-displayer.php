<?php

namespace Yoast\WP\Duplicate_Post\UI;

use Yoast\WP\Duplicate_Post\Permissions_Helper;
use Yoast\WP\Duplicate_Post\Utils;

class Post_Link_Displayer {

	/**
	 * @var \WP_Post
	 */
	private $post;

	/**
	 * @var Permissions_Helper
	 */
	private $permissions_helper;

	/**
	 * @var Link_Builder
	 */
	private $link_builder;

	/**
	 * Post_Wrapper constructor.
	 *
	 * @param \WP_Post $post The post object to wrap.
	 */
	public function __construct( \WP_Post $post, Permissions_Helper $permissions_helper, Link_Builder $link_builder ) {
		$this->post               = $post;
		$this->permissions_helper = $permissions_helper;
		$this->link_builder       = $link_builder;
	}

	/**
	 * Generates a New Draft permalink for the current post.
	 *
	 * @return string The permalink. Returns empty if the post can't be copied.
	 */
	public function get_new_draft_permalink() {
		if ( ! $this->post instanceof \WP_Post || ! $this->permissions_helper->should_links_be_displayed( $this->post ) ) {
			return '';
		}

		return $this->link_builder->build_new_draft_link( $this->post );
	}


	/**
	 * Generates a Rewrite & Republish permalink for the current post.
	 *
	 * @return string The permalink. Returns empty if the post cannot be copied for Rewrite & Republish.
	 */
	public function get_rewrite_republish_permalink() {
		if (
			! $this->post instanceof \WP_Post
			|| $this->permissions_helper->is_rewrite_and_republish_copy( $this->post )
			|| $this->permissions_helper->has_rewrite_and_republish_copy( $this->post )
			|| ! $this->permissions_helper->should_links_be_displayed( $this->post )
		) {
			return '';
		}

		return $this->link_builder->build_rewrite_and_republish_link( $this->post );
	}

	/**
	 * Generates a Check Changes permalink for the current post, if it's intended for Rewrite & Republish.
	 *
	 * @return string The permalink. Returns empty if the post does not exist or it's not a Rewrite & Republish copy.
	 */
	public function get_check_permalink() {
		if ( ! $this->post instanceof \WP_Post || ! $this->permissions_helper->is_rewrite_and_republish_copy( $this->post ) ) {
			return '';
		}

		return $this->link_builder->build_check_link( $this->post );
	}

	/**
	 * Generates a URL to the original post edit screen.
	 *
	 * @return string The URL. Empty if the copy post doesn't have an original.
	 */
	public function get_original_post_edit_url() {
		if ( ! $this->post instanceof \WP_Post || ! $this->permissions_helper->is_rewrite_and_republish_copy( $this->post ) ) {
			return '';
		}

		$original_post_id = Utils::get_original_post_id( $this->post->ID );

		if ( ! $original_post_id ) {
			return '';
		}

		return \add_query_arg(
			[
				'dprepublished' => 1,
				'dpcopy'        => $this->post->ID,
				'dpnonce'       => \wp_create_nonce( 'dp-republish' ),
			],
			\admin_url( 'post.php?action=edit&post=' . $original_post_id )
		);
	}

	/**
	 * Gets the title of the original post.
	 **
	 * @return string The original post title.
	 */
	public function get_original_post_title() {
		if ( ! $this->post instanceof \WP_Post || $this->permissions_helper->is_rewrite_and_republish_copy( $this->post ) ) {
			return '';
		}

		$original_post_id = Utils::get_original_post_id( $this->post->ID );

		if ( ! $original_post_id ) {
			return '';
		}

		return \_draft_or_post_title( $original_post_id );
	}

	/**
	 * Gets the title of the original post.
	 *
	 * @return string The original post title.
	 */
	public function get_original_post_edit_or_view_url() {
		if ( ! $this->post instanceof \WP_Post || $this->permissions_helper->is_rewrite_and_republish_copy( $this->post ) ) {
			return '';
		}

		$original_post_id = Utils::get_original_post_id( $this->post->ID );

		if ( ! $original_post_id ) {
			return '';
		}

		return Utils::get_edit_or_view_url( \get_post( $original_post_id ) );
	}

	/**
	 * Gets the aria-label for the link to of the original post.
	 *
	 * @return string The  aria-label for the link to of the original post.
	 */
	public function get_original_post_aria_label() {
		if ( ! $this->post instanceof \WP_Post || $this->permissions_helper->is_rewrite_and_republish_copy( $this->post ) ) {
			return '';
		}

		$original_post_id = Utils::get_original_post_id( $this->post->ID );

		if ( ! $original_post_id ) {
			return '';
		}

		return Utils::get_edit_or_view_aria_label( \get_post( $original_post_id ) );
	}
}
