<?php

namespace Wikibase\Repo\Notifications;

use Wikibase\Change;

/**
 * Channel for sending notifications about changes on the repo to any clients.
 *
 * @since 0.5
 *
 * @license GPL-2.0+
 * @author Daniel Kinzler
 */
interface ChangeTransmitter {

	/**
	 * Sends the given change over the channel.
	 *
	 * @since 0.5
	 *
	 * @param Change $change
	 *
	 * @throws ChangeTransmitterException
	 */
	public function transmitChange( Change $change );

}
