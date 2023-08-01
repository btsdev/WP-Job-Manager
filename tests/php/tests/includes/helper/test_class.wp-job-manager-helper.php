<?php
/**
 * @group helper
 * @group helper-base
 */
class WP_Test_WP_Job_Manager_Helper extends WPJM_BaseTest {

	public function testPluginLinks_InvalidLicense_AddsManageLicense() {
		// Arrange.
		$this->enable_update_plugins_cap();
		$instance = $this->getMockBuilder( WP_Job_Manager_Helper::class )
			->onlyMethods( [ 'get_licence_managed_plugin', 'get_plugin_licence' ] )
			->getMock();

		$instance->method( 'get_licence_managed_plugin' )->willReturn(
			[
				'_product_slug' => 'test',
				'_filename'     => 'test/test.php',
				'Version'       => '1.0.0',
				'Name'          => 'Test',
			]
		);

		$instance->method( 'get_plugin_licence' )->willReturn(
			[
				'licence_key' => 'xxxx-xxxx-xxxx-xxxx',
				'email'       => 'me@example.com',
				'errors'      => [
					'invalid_key' => 'Invalid license key',
				],
			]
		);

		// Act.
		$actions = $instance->plugin_links( [], 'test/test.php' );
		$this->disable_update_plugins_cap();

		// Assert.
		$this->assertCount( 1, $actions );
		$this->assertStringContainsString( __( 'Requires Attention', 'wp-job-manager' ), $actions[0] );
	}

	public function testPluginLinks_NoLicense_AddsActivateicense() {
		// Arrange.
		$this->enable_update_plugins_cap();
		$instance = $this->getMockBuilder( WP_Job_Manager_Helper::class )
			->onlyMethods( [ 'get_licence_managed_plugin', 'get_plugin_licence' ] )
			->getMock();

		$instance->method( 'get_licence_managed_plugin' )->willReturn(
			[
				'_product_slug' => 'test',
				'_filename'     => 'test/test.php',
				'Version'       => '1.0.0',
				'Name'          => 'Test',
			]
		);

		$instance->method( 'get_plugin_licence' )->willReturn(
			[
				'licence_key' => null,
				'email'       => null,
				'errors'      => null,
			]
		);

		// Act.
		$actions = $instance->plugin_links( [], 'test/test.php' );
		$this->disable_update_plugins_cap();

		// Assert.
		$this->assertCount( 1, $actions );
		$this->assertStringContainsString( __( 'Activate License', 'wp-job-manager' ), $actions[0] );
	}

	public function testPluginLinks_InvalidPlugin_NoActions() {
		// Arrange.
		$this->enable_update_plugins_cap();
		$instance = $this->getMockBuilder( WP_Job_Manager_Helper::class )
			->onlyMethods( [ 'get_licence_managed_plugin' ] )
			->getMock();

		$instance->method( 'get_licence_managed_plugin' )->willReturn( false );

		// Act.
		$actions = $instance->plugin_links( [], 'test/test.php' );
		$this->disable_update_plugins_cap();

		// Assert.
		$this->assertCount( 0, $actions );
	}

	public function testPluginLinks_NoCaps_NoActions() {
		// Arrange.
		$instance = $this->getMockBuilder( WP_Job_Manager_Helper::class )
			->onlyMethods( [ 'get_licence_managed_plugin' ] )
			->getMock();

		$instance->method( 'get_licence_managed_plugin' )->willReturn(
			[
				'_product_slug' => 'test',
				'_filename'     => 'test/test.php',
				'Version'       => '1.0.0',
				'Name'          => 'Test',
			]
		);


		// Act.
		$actions = $instance->plugin_links( [], 'test/test.php' );

		// Assert.
		$this->assertCount( 0, $actions );
	}

	public function testIsProductInstalled_WithKnownSlug_ReturnsTrue() {
		// Arrange.
		$instance = $this->getMockBuilder( WP_Job_Manager_Helper::class )
			->onlyMethods( [ 'get_installed_plugins' ] )
			->getMock();

		$instance->method( 'get_installed_plugins' )->willReturn(
			[
				'test' => [
					'_product_slug' => 'test',
					'_filename'     => 'test/test.php',
					'Version'       => '1.0.0',
					'Name'          => 'Test',
				],
			]
		);

		// Act.
		$result = $instance->is_product_installed( 'test' );

		// Assert.
		$this->assertTrue( $result );
	}

	public function testIsProductInstalled_WithUnknownSlug_ReturnsTrue() {
		// Arrange.
		$instance = $this->getMockBuilder( WP_Job_Manager_Helper::class )
			->onlyMethods( [ 'get_installed_plugins' ] )
			->getMock();

		$instance->method( 'get_installed_plugins' )->willReturn(
			[
				'test' => [
					'_product_slug' => 'test',
					'_filename'     => 'test/test.php',
					'Version'       => '1.0.0',
					'Name'          => 'Test',
				],
			]
		);

		// Act.
		$result = $instance->is_product_installed( 'rhino' );

		// Assert.
		$this->assertFalse( $result );
	}

	public function testHasLicencedProducts_WithLicencedProduct_ReturnsTrue() {
		// Arrange.
		$instance = $this->getMockBuilder( WP_Job_Manager_Helper::class )
			->onlyMethods( [ 'get_installed_plugins' ] )
			->getMock();

		$instance->method( 'get_installed_plugins' )->willReturn(
			[
				'test' => [
					'_product_slug' => 'test',
					'_filename'     => 'test/test.php',
					'Version'       => '1.0.0',
					'Name'          => 'Test',
				],
			]
		);

		// Act.
		$result = $instance->has_licenced_products();

		// Assert.
		$this->assertTrue( $result );
	}


	public function testHasLicencedProducts_WithoutLicencedProduct_ReturnsFalse() {
		// Arrange.
		$instance = $this->getMockBuilder( WP_Job_Manager_Helper::class )
			->onlyMethods( [ 'get_installed_plugins' ] )
			->getMock();

		$instance->method( 'get_installed_plugins' )->willReturn( [] );

		// Act.
		$result = $instance->has_licenced_products();

		// Assert.
		$this->assertFalse( $result );
	}

	public function testGetPluginLicence_WithLicense_ReturnsLicense() {
		// Arrange.
		$license_key = '1234';
		$email       = 'me@example.com';
		$errors      = [ 'error' ];

		WP_Job_Manager_Helper_Options::update( 'test', 'licence_key', $license_key );
		WP_Job_Manager_Helper_Options::update( 'test', 'email', $email );
		WP_Job_Manager_Helper_Options::update( 'test', 'errors', $errors );

		$instance = new WP_Job_Manager_Helper();

		// Act.
		$result = $instance->get_plugin_licence( 'test' );
		WP_Job_Manager_Helper_Options::delete( 'test', 'licence_key' );
		WP_Job_Manager_Helper_Options::delete( 'test', 'email' );
		WP_Job_Manager_Helper_Options::delete( 'test', 'errors' );

		// Assert.
		$this->assertEquals(
			[
				'licence_key' => $license_key,
				'email'       => $email,
				'errors'      => $errors,
			],
			$result
		);
	}

	public function testGetPluginLicence_WithoutLicense_ReturnsLicense() {
		// Arrange.
		$license_key = '1234';
		$email       = 'me@example.com';
		$errors      = [ 'error' ];

		WP_Job_Manager_Helper_Options::update( 'test', 'licence_key', $license_key );
		WP_Job_Manager_Helper_Options::update( 'test', 'email', $email );
		WP_Job_Manager_Helper_Options::update( 'test', 'errors', $errors );

		$instance = new WP_Job_Manager_Helper();

		// Act.
		$result = $instance->get_plugin_licence( 'rhino' );
		WP_Job_Manager_Helper_Options::delete( 'test', 'licence_key' );
		WP_Job_Manager_Helper_Options::delete( 'test', 'email' );
		WP_Job_Manager_Helper_Options::delete( 'test', 'errors' );

		// Assert.
		$this->assertEquals(
			[
				'licence_key' => null,
				'email'       => null,
				'errors'      => null,
			],
			$result
		);
	}

	public function testExtraHeaders_Always_ReturnsWPJMProduct() {
		// Arrange.
		$instance = new WP_Job_Manager_Helper();

		// Act.
		$result = $instance->extra_headers( [] );

		// Assert.
		$expected = [ 'WPJM-Product' ];
		$this->assertEquals( $expected, $result );
	}
}
