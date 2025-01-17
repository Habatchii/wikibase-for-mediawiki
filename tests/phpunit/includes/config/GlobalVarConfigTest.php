<?php

class GlobalVarConfigTest extends MediaWikiTestCase {

	/**
	 * @covers GlobalVarConfig::newInstance
	 */
	public function testNewInstance() {
		$config = GlobalVarConfig::newInstance();
		$this->assertInstanceOf( 'GlobalVarConfig', $config );
		$this->maybeStashGlobal( 'wgBaz' );
		$GLOBALS['wgBaz'] = 'somevalue';
		// Check prefix is set to 'wg'
		$this->assertEquals( 'somevalue', $config->get( 'Baz' ) );
	}

	/**
	 * @covers GlobalVarConfig::__construct
	 * @dataProvider provideConstructor
	 */
	public function testConstructor( $prefix ) {
		$var = $prefix . 'GlobalVarConfigTest';
		$rand = wfRandomString();
		$this->maybeStashGlobal( $var );
		$GLOBALS[$var] = $rand;
		$config = new GlobalVarConfig( $prefix );
		$this->assertInstanceOf( 'GlobalVarConfig', $config );
		$this->assertEquals( $rand, $config->get( 'GlobalVarConfigTest' ) );
	}

	public static function provideConstructor() {
		return array(
			array( 'wg' ),
			array( 'ef' ),
			array( 'smw' ),
			array( 'blahblahblahblah' ),
			array( '' ),
		);
	}

	public function provideGet() {
		$set = array(
			'wgSomething' => 'default1',
			'wgFoo' => 'default2',
			'efVariable' => 'default3',
			'BAR' => 'default4',
		);

		foreach ( $set as $var => $value ) {
			$GLOBALS[$var] = $value;
		}

		return array(
			array( 'Something', 'wg', 'default1' ),
			array( 'Foo', 'wg', 'default2' ),
			array( 'Variable', 'ef', 'default3' ),
			array( 'BAR', '', 'default4' ),
			array( 'ThisGlobalWasNotSetAbove', 'wg', false )
		);
	}

	/**
	 * @param string $name
	 * @param string $prefix
	 * @param string $expected
	 * @dataProvider provideGet
	 * @covers GlobalVarConfig::get
	 * @covers GlobalVarConfig::getWithPrefix
	 */
	public function testGet( $name, $prefix, $expected ) {
		$config = new GlobalVarConfig( $prefix );
		if ( $expected === false ) {
			$this->setExpectedException( 'ConfigException', 'GlobalVarConfig::getWithPrefix: undefined variable:' );
		}
		$this->assertEquals( $config->get( $name ), $expected );
	}

	public static function provideSet() {
		return array(
			array( 'Foo', 'wg', 'wgFoo' ),
			array( 'SomethingRandom', 'wg', 'wgSomethingRandom' ),
			array( 'FromAnExtension', 'eg', 'egFromAnExtension' ),
			array( 'NoPrefixHere', '', 'NoPrefixHere' ),
		);
	}

	private function maybeStashGlobal( $var ) {
		if ( array_key_exists( $var, $GLOBALS ) ) {
			// Will be reset after this test is over
			$this->stashMwGlobals( $var );
		}
	}

	/**
	 * @dataProvider provideSet
	 * @covers GlobalVarConfig::set
	 * @covers GlobalVarConfig::setWithPrefix
	 */
	public function testSet( $name, $prefix, $var ) {
		$this->hideDeprecated( 'GlobalVarConfig::set' );
		$this->maybeStashGlobal( $var );
		$config = new GlobalVarConfig( $prefix );
		$random = wfRandomString();
		$config->set( $name, $random );
		$this->assertArrayHasKey( $var, $GLOBALS );
		$this->assertEquals( $random, $GLOBALS[$var] );
	}
}
