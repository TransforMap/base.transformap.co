<?php

/**
 * Tests for the SiteArray class.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @since 1.20
 *
 * @ingroup Site
 * @ingroup Test
 *
 * @group Site
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SiteArrayTest extends GenericArrayObjectTest {

	/**
	 * @return array
	 */
	protected function getTestSites() {
		$sites = array();

		$site = Sites::newSite( 'enwiki' );
		$site->setId( 1 );
		$sites[] = $site;

		$site = Sites::newSite( 'dewiki' );
		$site->setId( 1 );
		$sites[] = $site;

		return $sites;
	}

	/**
	 * @see GenericArrayObjectTest::elementInstancesProvider
	 *
	 * @since 1.20
	 *
	 * @return array
	 */
	public function elementInstancesProvider() {
		$sites = iterator_to_array( Sites::singleton()->getSites() );

		$siteArrays = array();

		if ( count( $sites ) > 0 ) {
			$siteArrays[] = array( array_shift( $sites ) );
		}

		if ( count( $sites ) > 1 ) {
			$siteArrays[] = array( array_shift( $sites ), array_shift( $sites ) );
		}

		return $this->arrayWrap( $siteArrays );
	}

	/**
	 * @see GenericArrayObjectTest::getInstanceClass
	 *
	 * @since 1.20
	 *
	 * @return array
	 */
	public function getInstanceClass() {
		return 'SiteArray';
	}

	public function testUnset() {
		$sites = Sites::singleton()->getAllSites();

		if ( !$sites->isEmpty() ) {
			$offset = $sites->getIterator()->key();
			$count = $sites->count();
			$sites->offsetUnset( $offset );
			$this->assertEquals( $count - 1, $sites->count() );
		}

		if ( !$sites->isEmpty() ) {
			$offset = $sites->getIterator()->key();
			$count = $sites->count();
			unset( $sites[$offset] );
			$this->assertEquals( $count - 1, $sites->count() );
		}

		$exception = null;
		try { $sites->offsetUnset( 'sdfsedtgsrdysftu' ); } catch ( Exception $exception ){}
		$this->assertInstanceOf( 'Exception', $exception );
	}

	public function siteArrayProvider() {
		$sites = Sites::singleton()->getAllSites()->getArrayCopy();

		$siteArrays = array( array( array() ) );

		if ( count( $sites ) > 0 ) {
			$siteArrays[] = array( array( array_shift( $sites ) ) );
		}

		if ( count( $sites ) > 1 ) {
			$siteArrays[] = array( array( array_shift( $sites ), array_shift( $sites ) ) );
		}

		return $siteArrays;
	}

	/**
	 * @dataProvider siteArrayProvider
	 * @param array $siteArray
	 */
	public function testConstructor( array $siteArray ) {
		$siteList = new SiteArray( $siteArray );

		$this->assertEquals( count( $siteArray ), $siteList->count() );
	}

	/**
	 * @dataProvider siteArrayProvider
	 * @param array $siteArray
	 */
	public function testIsEmpty( array $siteArray ) {
		$siteList = new SiteArray( $siteArray );

		$this->assertEquals( $siteArray === array(), $siteList->isEmpty() );
	}

	public function testGetSiteByLocalId() {
		$sites = Sites::singleton()->getAllSites();

		if ( $sites->isEmpty() ) {
			$this->markTestSkipped( 'No sites to test with' );
		}
		else {
			$site = $sites->getIterator()->current();
			$this->assertEquals( $site, $sites->getSiteByLocalId( $site->getConfig()->getLocalId() ) );
		}
	}

	public function testHasLocalId() {
		$sites = Sites::singleton()->getAllSites();

		if ( $sites->isEmpty() ) {
			$this->markTestSkipped( 'No sites to test with' );
		}
		else {
			$site = $sites->getIterator()->current();
			$this->assertTrue( $sites->hasLocalId( $site->getConfig()->getLocalId() ) );
			$this->assertFalse( $sites->hasLocalId( 'dxzfzxdegxdrfyxsdty' ) );
		}
	}

	public function testGetSiteByGlobalId() {
		$sites = Sites::singleton()->getAllSites();

		if ( $sites->isEmpty() ) {
			$this->markTestSkipped( 'No sites to test with' );
		}
		else {
			$site = $sites->getIterator()->current();
			$this->assertEquals( $site, $sites->getSiteByGlobalId( $site->getGlobalId() ) );
		}
	}

	public function testHasGlobalId() {
		$sites = Sites::singleton()->getAllSites();

		if ( $sites->isEmpty() ) {
			$this->markTestSkipped( 'No sites to test with' );
		}
		else {
			$site = $sites->getIterator()->current();
			$this->assertTrue( $sites->hasGlobalId( $site->getGlobalId() ) );
			$this->assertFalse( $sites->hasGlobalId( 'dxzfzxdegxdrfyxsdty' ) );
		}
	}

	public function testGetGroup() {
		$allSites = Sites::singleton()->getAllSites();
		$count = 0;

		foreach ( $allSites->getGroupNames() as $groupName ) {
			$group = Sites::singleton()->getGroup( $groupName );
			$this->assertInstanceOf( 'SiteList', $group );
			$count += $group->count();

			if ( !$group->isEmpty() ) {
				$sites = iterator_to_array( $group );

				foreach ( array_slice( $sites, 0, 5 ) as $site ) {
					$this->assertInstanceOf( 'Site', $site );
					$this->assertEquals( $groupName, $site->getGroup() );
				}
			}
		}

		$this->assertEquals( $allSites->count(), $count );
	}

	public function testGetGroupNames() {
		$allSites = Sites::singleton()->getAllSites();
		$groups = array();

		foreach ( $allSites as $site ) {
			$groups[] = $site->getGroup();
		}

		$groups = array_unique( $groups );
		$obtainedGroups = $allSites->getGroupNames();

		asort( $groups );
		asort( $obtainedGroups );

		$this->assertEquals(
			array_values( $groups ),
			array_values( $obtainedGroups )
		);
	}

	public function siteListProvider() {
		$sites = Sites::singleton();
		$groups = $sites->getAllSites()->getGroupNames();
		$group = array_shift( $groups );

		return array(
			array( $sites->getAllSites() ),
			array( $sites->getGroup( $group ), $group ),
			array( new SiteArray() ),
		);
	}

	/**
	 * @dataProvider siteListProvider
	 * @param SiteList $sites
	 */
	public function testGetGlobalIdentifiers( SiteList $sites, $groupName = null ) {
		$identifiers = $sites->getGlobalIdentifiers( $groupName );

		$this->assertTrue( is_array( $identifiers ) );

		$expected = array();

		foreach ( $sites as $site ) {
			$expected[] = $site->getGlobalId();
		}

		asort( $expected );
		asort( $identifiers );

		$this->assertEquals(
			array_values( $expected ),
			array_values( $identifiers )
		);
	}

	public function testGetLocalIdentifiers() {
		$allSites = Sites::singleton()->getAllSites();
		$identifiers = $allSites->getLocalIdentifiers();

		$this->assertTrue( is_array( $identifiers ) );
	}

	public function testAppend() {
		$sites = array_slice( Sites::singleton()->getAllSites()->getArrayCopy(), 0, 5 );

		if ( count( $sites ) === 5 ) {
			$list = new SiteArray();

			foreach ( $sites as $site ) {
				$list->append( $site );
				$list->hasGlobalId( $site->getGlobalId() );
			}

			$list = new SiteArray();

			foreach ( $sites as $site ) {
				$list[] = $site;
				$list->hasGlobalId( $site->getGlobalId() );
			}

			$listSize = $list->count();

			$list = new SiteArray();

			foreach ( $sites as $site ) {
				$list[] = $site;
				$list->append( $site );
			}

			$this->assertEquals( $listSize, $list->count() );

			$excption = null;

			try{
				$list->append( 42 );
				$this->fail( 'Appending an integer to a SiteList should not work' );
			}
			catch ( MWException $excption ) {}

		}
		else {
			$this->markTestSkipped( 'No sites to test with' );
		}
	}

	public function testOffsetSet() {
		$sites = array_slice( Sites::singleton()->getAllSites()->getArrayCopy(), 0, 5 );

		if ( count( $sites ) === 5 ) {
			$list = new SiteArray();

			$site = array_shift( $sites );
			$list->offsetSet( 42, $site );
			$this->assertEquals( $site, $list->offsetGet( 42 ) );

			$site = array_shift( $sites );
			$list['oHai'] = $site;
			$this->assertEquals( $site, $list['oHai'] );

			$site = array_shift( $sites );
			$list->offsetSet( 9001, $site );
			$this->assertEquals( $site, $list[9001] );

			$site = array_shift( $sites );
			$list->offsetSet( null, $site );
			$this->assertEquals( $site, $list[0] );

			$site = array_shift( $sites );
			$list->offsetSet( null, $site );
			$this->assertEquals( $site, $list[1] );
		}
		else {
			$this->markTestSkipped( 'No sites to test with' );
		}
	}

}