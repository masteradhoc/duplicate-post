<?php

namespace Yoast\WP\Duplicate_Post;

/**
 * Class to manage the meta field storing the ID of the original post.
 *
 * @since 4.0
 */
class Meta_Field {

	public const META_FIELD = '_dp_original';

	/**
	 * Registers hooks to integrate with WordPress.
	 *
	 * @return void
	 */
	public function register_hooks() {
		\add_action( 'init', [ $this, 'register_meta' ] );
		\add_filter( 'is_protected_meta', [ $this, 'filter_protected_meta' ], 10, 2 );
	}

	/**
	 * Registers the `_dp_original` meta.
	 *
	 * @return void
	 */
	public function register_meta() {
		\register_post_meta(
			'',
			self::META_FIELD,
			[
				'type'         => 'integer',
				'single'       => true,
				'show_in_rest' => true,
			]
		);
	}

	/**
	 * Filters whether the meta key is protected.
	 *
	 * @param bool   $is_protected Whether the meta key is protected.
	 * @param string $meta_key     The metadata key.
	 *
	 * @return bool
	 */
	public function filter_protected_meta( $is_protected, $meta_key ) {
		if ( $meta_key === self::META_FIELD ) {
			return false;
		}
		return $is_protected;
	}
}
