<?php

namespace Wikibase\Repo\Store;

use Wikibase\Lib\Store\EntityTitleLookup;

/**
 * Represents a mapping from entity IDs to wiki page titles, assuming that the resulting title
 * represents a page that actually stores the entity contents. For example, the property P1 will be
 * resolved to the "Property" namespace and the page "Property:P1".
 *
 * The mapping could be programmatic, or it could be based on database lookups.
 *
 * @since 0.5
 *
 * @license GPL-2.0+
 * @author Daniel Kinzler
 * @author Thiemo Mättig
 */
interface EntityTitleStoreLookup extends EntityTitleLookup {

}
