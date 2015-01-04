<?php

namespace SBL\Tests;

use SBL\HookRegistry;

use HashBagOStuff;
use Title;

/**
 * @covers \SBL\HookRegistry
 *
 * @group semantic-interlanguage-links
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class HookRegistryTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$configuration = array();

		$this->assertInstanceOf(
			'\SBL\HookRegistry',
			new HookRegistry(
				$store,
				$configuration,
				$this->getMock( 'SBL\PropertyRegistry' )
			)
		);
	}

	public function testRegister() {

		$title = Title::newFromText( __METHOD__ );

		$outputPage = $this->getMockBuilder( '\OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$outputPage->expects( $this->any() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$skin = $this->getMockBuilder( '\Skin' )
			->disableOriginalConstructor()
			->getMock();

		$skin->expects( $this->any() )
			->method( 'getOutput' )
			->will( $this->returnValue( $outputPage ) );

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$configuration = array(
			'useSubpageDiscoveryForFallback' => false,
			'maxAntecedentHierarchyMatchDepth' => 3,
			'tryToFindClosestDescendant' => false,
			'propertySearchPatternByNamespace' => array(),
			'breadcrumbTrailStyleClass' => 'foo'
		);

		$wgHooks = array();

		$instance = new HookRegistry(
			$store,
			$configuration,
			$this->getMock( 'SBL\PropertyRegistry' )
		);
		$instance->register( $wgHooks );

		$this->assertNotEmpty(
			$wgHooks
		);

		$this->assertHookIsExcutable(
			$wgHooks,
			'smwInitProperties',
			array()
		);

		$template = new \stdClass;

		$this->assertHookIsExcutable(
			$wgHooks,
			'SkinTemplateOutputPageBeforeExec',
			array( &$skin, &$template )
		);

		$this->assertHookIsExcutable(
			$wgHooks,
			'BeforePageDisplay',
			array( &$outputPage, &$skin )
		);
	}

	private function assertHookIsExcutable( $wgHooks, $hookName, $arguments ) {
		foreach ( $wgHooks[ $hookName ] as $hook ) {
			$this->assertInternalType(
				'boolean',
				call_user_func_array( $hook, $arguments )
			);
		}
	}

}
