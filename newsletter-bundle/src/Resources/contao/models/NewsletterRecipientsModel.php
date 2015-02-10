<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao;


/**
 * Reads and writes newsletter recipients
 *
 * @property integer $id
 * @property integer $pid
 * @property integer $tstamp
 * @property string  $email
 * @property boolean $active
 * @property string  $source
 * @property string  $addedOn
 * @property string  $confirmed
 * @property string  $ip
 * @property string  $token
 *
 * @method static $this findById()
 * @method static $this findOneByPid()
 * @method static $this findOneByTstamp()
 * @method static $this findOneByEmail()
 * @method static $this findOneByActive()
 * @method static $this findOneBySource()
 * @method static $this findOneByAddedOn()
 * @method static $this findOneByConfirmed()
 * @method static $this findOneByIp()
 * @method static $this findOneByToken()
 * @method static \Model\Collection findByPid()
 * @method static \Model\Collection findByTstamp()
 * @method static \Model\Collection findByEmail()
 * @method static \Model\Collection findByActive()
 * @method static \Model\Collection findBySource()
 * @method static \Model\Collection findByAddedOn()
 * @method static \Model\Collection findByConfirmed()
 * @method static \Model\Collection findByIp()
 * @method static \Model\Collection findByToken()
 * @method static integer countById()
 * @method static integer countByPid()
 * @method static integer countByTstamp()
 * @method static integer countByEmail()
 * @method static integer countByActive()
 * @method static integer countBySource()
 * @method static integer countByAddedOn()
 * @method static integer countByConfirmed()
 * @method static integer countByIp()
 * @method static integer countByToken()
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class NewsletterRecipientsModel extends \Model
{

	/**
	 * Table name
	 * @var string
	 */
	protected static $strTable = 'tl_newsletter_recipients';


	/**
	 * Find recipients by their e-mail address and parent ID
	 *
	 * @param string $strEmail   The e-mail address
	 * @param array  $arrPids    An array of newsletter channel IDs
	 * @param array  $arrOptions An optional options array
	 *
	 * @return \Model\Collection|null A collection of models or null if there are no recipients
	 */
	public static function findByEmailAndPids($strEmail, $arrPids, array $arrOptions=array())
	{
		if (!is_array($arrPids) || empty($arrPids))
		{
			return null;
		}

		$t = static::$strTable;

		return static::findBy(array("$t.email=? AND $t.pid IN(" . implode(',', array_map('intval', $arrPids)) . ")"), $strEmail, $arrOptions);
	}
}
