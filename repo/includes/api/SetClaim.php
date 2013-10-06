<?php

namespace Wikibase\Api;

use ApiMain;
use ApiBase;
use MWException;
use DataValues\IllegalValueException;
use Diff\Comparer\ComparableComparer;
use Diff\OrderedListDiffer;
use FormatJson;
use Diff\ListDiffer;
use ValueFormatters\FormatterOptions;
use Wikibase\ChangeOp\ChangeOpClaim;
use Wikibase\ChangeOp\ChangeOpException;
use Wikibase\ClaimDiffer;
use Wikibase\Claims;
use Wikibase\ClaimSummaryBuilder;
use Wikibase\EntityContent;
use Wikibase\Claim;
use Wikibase\EntityContentFactory;
use Wikibase\Lib\ClaimGuidGenerator;
use Wikibase\Lib\Serializers\SerializerFactory;
use Wikibase\Lib\SnakFormatter;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\Summary;

/**
 * API module for creating or updating an entire Claim.
 *
 * @since 0.4
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Tobias Gritschacher < tobias.gritschacher@wikimedia.de >
 * @author Adam Shorland
 */
class SetClaim extends ModifyClaim {

	/**
	 * @see ApiBase::execute
	 *
	 * @since 0.4
	 */
	public function execute() {
		$params = $this->extractRequestParams();
		$claim = $this->getClaimFromParams( $params );
		$this->snakValidation->validateClaimSnaks( $claim );

		$guid = $claim->getGuid();
		if( $guid === null ){
			$this->dieUsage( 'GUID must be set when setting a claim', 'invalid-claim' );
		}
		$guid = $this->claimGuidParser->parse( $guid );

		$entityId = $guid->getEntityId();
		$entityContentFactory = WikibaseRepo::getDefaultInstance()->getEntityContentFactory();
		$entityContent = $entityContentFactory->getFromId( $entityId );
		$entity = $entityContent->getEntity();
		$summary = $this->getSummary( $params, $claim, $entityContent );

		$changeop = new ChangeOpClaim( $claim , new ClaimGuidGenerator( $guid->getEntityId() ) );
		try{
			$changeop->apply( $entity, $summary );
		} catch( ChangeOpException $exception ){
			$this->dieUsage( 'Failed to apply changeOp:' . $exception->getMessage(), 'save-failed' );
		}

		$this->saveChanges( $entityContent, $summary );
		$this->claimModificationHelper->addClaimToApiResult( $claim );
	}

	/**
	 * @param array $params
	 * @param Claim $claim
	 * @param EntityContent $entityContent
	 * @return Summary
	 * @todo this summary builder is ugly and summary stuff needs to be refactored
	 */
	protected function getSummary( array $params, Claim $claim, EntityContent $entityContent ){
		$claimSummaryBuilder = new ClaimSummaryBuilder(
			$this->getModuleName(),
			new ClaimDiffer( new OrderedListDiffer( new ComparableComparer() ) ),
			WikibaseRepo::getDefaultInstance()->getSnakFormatterFactory()->getSnakFormatter(
				SnakFormatter::FORMAT_PLAIN,
				new FormatterOptions()
			)
		);
		$summary = $claimSummaryBuilder->buildClaimSummary(
			new Claims( $entityContent->getEntity()->getClaims() ),
			$claim
		);
		if ( isset( $params['summary'] ) ) {
			$summary->setUserSummary( $params['summary'] );
		}
		return $summary;
	}

	/**
	 * @since 0.4
	 * @param array $params
	 * @return Claim
	 */
	protected function getClaimFromParams( array $params ) {
		$serializerFactory = new SerializerFactory();
		$unserializer = $serializerFactory->newUnserializerForClass( 'Wikibase\Claim' );

		try {
			$claim = $unserializer->newFromSerialization( FormatJson::decode( $params['claim'], true ) );
			if( !$claim instanceof Claim ) {
				$this->dieUsage( 'Failed to get claim from claim Serialization', 'invalid-claim' );
			}
			return $claim;
		} catch ( IllegalValueException $illegalValueException ) {
			$this->dieUsage( $illegalValueException->getMessage(), 'invalid-claim' );
		} catch( MWException $mwException ) {
			$this->dieUsage( 'Failed to get claim from claim Serialization ' . $mwException->getMessage(), 'invalid-claim' );
		}
	}

	/**
	 * @see ApiBase::getAllowedParams
	 *
	 * @since 0.4
	 *
	 * @return array
	 */
	public function getAllowedParams() {
		return array_merge(
			array(
				'claim' => array(
					ApiBase::PARAM_TYPE => 'string',
					ApiBase::PARAM_REQUIRED => true
				)
			),
			parent::getAllowedParams()
		);
	}

	/**
	 * @see ApiBase::getPossibleErrors()
	 */
	public function getPossibleErrors() {
		return array_merge( parent::getPossibleErrors(), array(
			array( 'code' => 'invalid-claim', 'info' => $this->msg( 'wikibase-api-invalid-claim' )->text() ),
		) );
	}

	/**
	 * @see ApiBase::getParamDescription
	 *
	 * @since 0.4
	 *
	 * @return array
	 */
	public function getParamDescription() {
		return array_merge(
			parent::getParamDescription(),
			array(
				'claim' => 'Claim serialization'
			)
		);
	}

	/**
	 * @see ApiBase::getDescription
	 *
	 * @since 0.4
	 *
	 * @return string
	 */
	public function getDescription() {
		return array(
			'API module for creating or updating an entire Claim.'
		);
	}

	/**
	 * @see ApiBase::getExamples
	 *
	 * @since 0.4
	 *
	 * @return array
	 */
	protected function getExamples() {
		return array(
			'api.php?action=wbsetclaim&claim={"id":"Q2$5627445f-43cb-ed6d-3adb-760e85bd17ee","type":"claim","mainsnak":{"snaktype":"value","property":"P1","datavalue":{"value":"City","type":"string"}}}'
			=> 'Set the claim with the given id to property P1 with a string value of "City',
		);
	}

}
