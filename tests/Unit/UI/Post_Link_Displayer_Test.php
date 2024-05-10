<?php

namespace Yoast\WP\Duplicate_Post\Tests\Unit\UI;

use Brain\Monkey;
use Mockery;
use WP_Post;
use Yoast\WP\Duplicate_Post\Permissions_Helper;
use Yoast\WP\Duplicate_Post\Tests\Unit\TestCase;
use Yoast\WP\Duplicate_Post\UI\Post_Link_Displayer;
use Yoast\WP\Duplicate_Post\UI\Link_Builder;

/**
 * Test the Post_Link_Displayer class.
 */
final class Post_Link_Displayer_Test extends TestCase {

	/**
	 * Holds the post object.
	 *
	 * @var WP_Post|Mockery\MockInterface
	 */
	protected $post;

	/**
	 * Holds the permissions helper.
	 *
	 * @var Permissions_Helper|Mockery\MockInterface
	 */
	protected $permissions_helper;

	/**
	 * Holds the object to create the action link to duplicate.
	 *
	 * @var Link_Builder|Mockery\MockInterface
	 */
	protected $link_builder;

	/**
	 * The instance.
	 *
	 * @var Post_Link_Displayer|Mockery\MockInterface
	 */
	protected $instance;

	/**
	 * Sets the instance.
	 *
	 * @return void
	 */
	protected function set_up() {
		parent::set_up();

		$this->post              = Mockery::mock( WP_Post::class );
		$this->link_builder       = Mockery::mock( Link_Builder::class );
		$this->permissions_helper = Mockery::mock( Permissions_Helper::class );

		$this->instance = new Post_Link_Displayer(
			$this->post,
			$this->permissions_helper,
			$this->link_builder
		);
	}

	/**
	 * Tests if the needed attributes are set correctly.
	 *
	 * @covers \Yoast\WP\Duplicate_Post\UI\Post_Link_Displayer::__construct
	 *
	 * @return void
	 */
	public function test_constructor() {
		$this->assertInstanceOf(
			WP_Post::class,
			$this->getPropertyValue( $this->instance, 'post' )
		);

		$this->assertInstanceOf(
			Permissions_Helper::class,
			$this->getPropertyValue( $this->instance, 'permissions_helper' )
		);

		$this->assertInstanceOf(
			Link_Builder::class,
			$this->getPropertyValue( $this->instance, 'link_builder' )
		);
	}

	/**
	 * Tests the get_new_draft_permalink function when a link is returned.
	 *
	 * @covers \Yoast\WP\Duplicate_Post\UI\Post_Link_Displayer::get_new_draft_permalink
	 *
	 * @return void
	 */
	public function test_get_new_draft_permalink_successful() {
		$url = 'http://basic.wordpress.test/wp-admin/admin.php?action=duplicate_post_new_draft&post=201&_wpnonce=94038b7dee';
		$this->permissions_helper
			->expects( 'should_links_be_displayed' )
			->with( $this->post )
			->andReturnTrue();

		$this->link_builder
			->expects( 'build_new_draft_link' )
			->with( $this->post )
			->andReturn( $url );

		$this->assertSame( $url, $this->instance->get_new_draft_permalink() );
	}

	/**
	 * Tests the get_new_draft_permalink function when a link should not be
	 * displayed.
	 *
	 * @covers \Yoast\WP\Duplicate_Post\UI\Post_Link_Displayer::get_new_draft_permalink
	 *
	 * @return void
	 */
	public function test_get_new_draft_permalink_unsuccessful() {
		$this->permissions_helper
			->expects( 'should_links_be_displayed' )
			->with( $this->post )
			->andReturnFalse();

		$this->link_builder
			->expects( 'build_new_draft_link' )
			->with( $this->post )
			->never();

		$this->assertSame( '', $this->instance->get_new_draft_permalink() );
	}

	/**
	 * Tests the get_rewrite_republish_permalink function when a link is
	 * returned.
	 *
	 * @covers \Yoast\WP\Duplicate_Post\UI\Post_Link_Displayer::get_rewrite_republish_permalink
	 *
	 * @return void
	 */
	public function test_get_rewrite_republish_permalink_successful() {
		$url  = 'http://basic.wordpress.test/wp-admin/admin.php?action=duplicate_post_rewrite&post=201&_wpnonce=5e7abf68c9';

		$this->permissions_helper
			->expects( 'is_rewrite_and_republish_copy' )
			->with( $this->post )
			->andReturnFalse();

		$this->permissions_helper
			->expects( 'has_rewrite_and_republish_copy' )
			->with( $this->post )
			->andReturnFalse();

		$this->permissions_helper
			->expects( 'should_links_be_displayed' )
			->with( $this->post )
			->andReturnTrue();

		$this->link_builder
			->expects( 'build_rewrite_and_republish_link' )
			->with( $this->post )
			->andReturn( $url );

		$this->assertSame( $url, $this->instance->get_rewrite_republish_permalink() );
	}

	/**
	 * Tests the get_rewrite_republish_permalink function when the post is a Rewrite & Republish copy.
	 *
	 * @covers \Yoast\WP\Duplicate_Post\UI\Post_Link_Displayer::get_rewrite_republish_permalink
	 *
	 * @return void
	 */
	public function test_get_rewrite_republish_permalink_unsuccessful_is_rewrite_and_republish() {
		$this->permissions_helper
			->expects( 'is_rewrite_and_republish_copy' )
			->with( $this->post )
			->andReturnTrue();

		$this->permissions_helper
			->expects( 'has_rewrite_and_republish_copy' )
			->with( $this->post )
			->never();

		$this->permissions_helper
			->expects( 'should_links_be_displayed' )
			->with( $this->post )
			->never();

		$this->link_builder
			->expects( 'build_rewrite_and_republish_link' )
			->with( $this->post )
			->never();

		$this->assertSame( '', $this->instance->get_rewrite_republish_permalink() );
		$this->assertTrue( Monkey\Filters\applied( 'duplicate_post_show_link' ) === 0 );
	}

	/**
	 * Tests the get_rewrite_republish_permalink function when the post has a Rewrite & Republish copy.
	 *
	 * @covers \Yoast\WP\Duplicate_Post\UI\Post_Link_Displayer::get_rewrite_republish_permalink
	 *
	 * @return void
	 */
	public function test_get_rewrite_republish_permalink_unsuccessful_has_a_rewrite_and_republish() {
		$this->permissions_helper
			->expects( 'is_rewrite_and_republish_copy' )
			->with( $this->post )
			->andReturnFalse();

		$this->permissions_helper
			->expects( 'has_rewrite_and_republish_copy' )
			->with( $this->post )
			->andReturnTrue();

		$this->permissions_helper
			->expects( 'should_links_be_displayed' )
			->with( $this->post )
			->never();

		$this->link_builder
			->expects( 'build_rewrite_and_republish_link' )
			->with( $this->post )
			->never();

		$this->assertSame( '', $this->instance->get_rewrite_republish_permalink() );
	}

	/**
	 * Tests the get_rewrite_republish_permalink function when the links should not be displayed.
	 *
	 * @covers \Yoast\WP\Duplicate_Post\UI\Post_Link_Displayer::get_rewrite_republish_permalink
	 *
	 * @return void
	 */
	public function test_get_rewrite_republish_permalink_unsuccessful_links_should_not_be_displayed() {
		$this->post->post_status = 'publish';

		$this->permissions_helper
			->expects( 'is_rewrite_and_republish_copy' )
			->with( $this->post )
			->andReturnFalse();

		$this->permissions_helper
			->expects( 'has_rewrite_and_republish_copy' )
			->with( $this->post )
			->andReturnFalse();

		$this->permissions_helper
			->expects( 'should_links_be_displayed' )
			->with( $this->post )
			->andReturnFalse();

		$this->link_builder
			->expects( 'build_rewrite_and_republish_link' )
			->with( $this->post )
			->never();

		$this->assertSame( '', $this->instance->get_rewrite_republish_permalink() );
	}

	/**
	 * Tests the get_check_permalink function when a link is returned.
	 *
	 * @covers \Yoast\WP\Duplicate_Post\UI\Post_Link_Displayer::get_check_permalink
	 *
	 * @return void
	 */
	public function test_get_check_permalink_successful() {
		$url = 'http://basic.wordpress.test/wp-admin/admin.php?action=duplicate_post_check_changes&post=201&_wpnonce=5e7abf68c9';

		$this->permissions_helper
			->expects( 'is_rewrite_and_republish_copy' )
			->with( $this->post )
			->andReturnTrue();

		$this->link_builder
			->expects( 'build_check_link' )
			->with( $this->post )
			->andReturn( $url );

		$this->assertSame( $url, $this->instance->get_check_permalink() );
	}

	/**
	 * Tests the get_check_permalink function when the post is not intended for
	 * Rewrite & Republish.
	 *
	 * @covers \Yoast\WP\Duplicate_Post\UI\Post_Link_Displayer::get_check_permalink
	 *
	 * @return void
	 */
	public function test_get_check_permalink_not_rewrite_and_republish() {
		$this->permissions_helper
			->expects( 'is_rewrite_and_republish_copy' )
			->with( $this->post )
			->andReturnFalse();

		$this->link_builder
			->expects( 'build_check_link' )
			->with( $this->post )
			->never();

		$this->assertSame( '', $this->instance->get_check_permalink() );
	}

	/**
	 * Tests the successful get_original_post_edit_url.
	 *
	 * @covers \Yoast\WP\Duplicate_Post\UI\Post_Link_Displayer::get_original_post_edit_url
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function test_get_original_post_edit_url_successful() {
		$utils          = Mockery::mock( 'alias:\Yoast\WP\Duplicate_Post\Utils' );
		$this->post->ID = 128;
		$original_id    = 64;
		$nonce           = '12345678';

		$this->permissions_helper
			->expects( 'is_rewrite_and_republish_copy' )
			->with( $this->post )
			->andReturnTrue();

		$utils
			->expects( 'get_original_post_id' )
			->with( $this->post->ID )
			->andReturn( $original_id );

		Monkey\Functions\expect( '\admin_url' )
			->andReturnUsing(
				static function ( $query_string ) {
					return 'http://basic.wordpress.test/wp-admin/' . $query_string;
				}
			);

		Monkey\Functions\expect( '\wp_create_nonce' )
			->with( 'dp-republish' )
			->andReturn( $nonce );

		Monkey\Functions\expect( '\add_query_arg' )
			->andReturnUsing(
				static function ( $arguments, $query_string ) {
					foreach ( $arguments as $key => $value ) {
						$query_string .= '&' . $key . '=' . $value;
					}

					return $query_string;
				}
			);

		$this->assertSame(
			'http://basic.wordpress.test/wp-admin/post.php?action=edit&post=64&dprepublished=1&dpcopy=128&dpnonce=12345678',
			$this->instance->get_original_post_edit_url()
		);
	}

	/**
	 * Tests the unsuccessful get_original_post_edit_url when the post is not a Rewrite & Republish copy.
	 *
	 * @covers \Yoast\WP\Duplicate_Post\UI\Post_Link_Displayer::get_original_post_edit_url
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function test_get_original_post_edit_url_not_rewrite_and_republish() {
		$this->post->ID = 128;

		$this->permissions_helper
			->expects( 'is_rewrite_and_republish_copy' )
			->with( $this->post )
			->andReturnFalse();

		$this->assertSame(
			'',
			$this->instance->get_original_post_edit_url()
		);
	}

	/**
	 * Tests the get_original_post_edit_url function when there is no original.
	 *
	 * @covers \Yoast\WP\Duplicate_Post\UI\Post_Link_Displayer::get_original_post_edit_url
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 *
	 * @return void
	 */
	public function test_get_original_post_edit_url_no_original() {
		$utils          = Mockery::mock( 'alias:\Yoast\WP\Duplicate_Post\Utils' );
		$this->post->ID = 128;
		$original_id    = '';

		$this->permissions_helper
			->expects( 'is_rewrite_and_republish_copy' )
			->with( $this->post )
			->andReturnTrue();

		$utils
			->expects( 'get_original_post_id' )
			->with( $this->post->ID )
			->andReturn( $original_id );

		$this->assertSame(
			'',
			$this->instance->get_original_post_edit_url()
		);
	}
}
