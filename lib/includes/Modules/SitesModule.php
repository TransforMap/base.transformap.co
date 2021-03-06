<?php

namespace Wikibase;

use MediaWiki\MediaWikiServices;
use ResourceLoaderContext;
use ResourceLoaderModule;
use Wikibase\Lib\SitesModuleWorker;

/**
 * @since 0.2
 *
 * @license GPL-2.0+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Daniel Werner < daniel.werner@wikimedia.de >
 * @author Marius Hoch < hoo@online.de >
 */
class SitesModule extends ResourceLoaderModule {

	/**
	 * @var SitesModuleWorker
	 */
	private $worker;

	public function __construct() {
		$this->worker = new SitesModuleWorker(
			Settings::singleton(),
			MediaWikiServices::getInstance()->getSiteStore(),
			wfGetCache( wfIsHHVM() ? CACHE_ACCEL : CACHE_ANYTHING )
		);
	}

	/**
	 * Used to propagate information about sites to JavaScript.
	 * Sites infos will be available in 'wbSiteDetails' config var.
	 * @see ResourceLoaderModule::getScript
	 *
	 * @since 0.2
	 *
	 * @param ResourceLoaderContext $context
	 *
	 * @return string
	 */
	public function getScript( ResourceLoaderContext $context ) {
		return $this->worker->getScript( $context->getLanguage() );
	}

	/**
	 * @see ResourceLoaderModule::getDefinitionSummary
	 *
	 * @param ResourceLoaderContext $context
	 *
	 * @return array
	 */
	public function getDefinitionSummary( ResourceLoaderContext $context ) {
		$summary = parent::getDefinitionSummary( $context );
		$summary[] = $this->worker->getDefinitionSummary();
		return $summary;
	}

}
