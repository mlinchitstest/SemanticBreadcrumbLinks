<?php

namespace SBL;

use SMW\DIWikiPage;
use SMW\ParserData;
use SMW\DIProperty;
use Title;

/**
 * @license GNU GPL v2+
 * @since 1.3
 *
 * @author mwjames
 */
class SubpageParentAnnotator {

	/**
	 * @var ParserData
	 */
	private $parserData;

	/**
	 * @var boolean
	 */
	private $subpageParentAnnotationState = true;

	/**
	 * @since 1.3
	 *
	 * @param ParserData $parserData
	 */
	public function __construct( ParserData $parserData ) {
		$this->parserData = $parserData;
	}

	/**
	 * @since 1.3
	 *
	 * @param boolean $subpageParentAnnotationState
	 */
	public function setSubpageParentAnnotationState( $subpageParentAnnotationState ) {
		$this->subpageParentAnnotationState = (bool)$subpageParentAnnotationState;
	}

	/**
	 * @since  1.3
	 */
	public function addAnnotation() {

		$title = $this->parserData->getTitle();

		if ( !$this->subpageParentAnnotationState || strpos( $title->getText(), '/' ) === false ) {
			return;
		}

		$property = DIProperty::newFromUserLabel( SBL_PROP_PARENTPAGE );

		// Don't override any "man"-made annotation
		if ( $this->parserData->getSemanticData()->getPropertyValues( $property ) !== array() ) {
			return;
		}

		$this->parserData->getSemanticData()->addPropertyObjectValue(
			$property,
			DIWikiPage::newFromText( $this->getBaseText( $title ), $title->getNamespace() )
		);

		$this->parserData->pushSemanticDataToParserOutput();
	}

	// Don't rely on Title::getBaseText as it depends on the wgNamespacesWithSubpages
	// setting and if set false will return the normal text including its subparts
	private function getBaseText( $title ) {

		$parts = explode( '/', $title->getText() );
		# Don't discard the real title if there's no subpage involved
		if ( count( $parts ) > 1 ) {
			unset( $parts[count( $parts ) - 1] );
		}

		return implode( '/', $parts );
	}

}
