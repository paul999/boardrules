<?php
/**
*
* Board Rules extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\boardrules\migrations\v20x;

class m14_reparse extends \phpbb\db\migration\container_aware_migration
{
	/**
	 * @inheritDoc
	 */
	static public function depends_on()
	{
		return array(
			'\phpbb\boardrules\migrations\v10x\m7_sample_rule_data',
			'\phpbb\boardrules\migrations\v20x\m13_font_icon',
		);
	}

	/**
	 * @inheritDoc
	 */
	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'reparse'))),
		);
	}

	/**
	 * Run the boardrules rule text reparser
	 *
	 * @param int $current A rule identifier
	 * @return bool|int A rule identifier or true if finished
	 */
	public function reparse($current = 0)
	{
		$reparser = new \phpbb\boardrules\textreparser\plugins\rule_text($this->db);
		$reparser->set_table_name($this->container->getParameter('core.table_prefix') . 'boardrules');

		if (empty($current))
		{
			$current = $reparser->get_max_id();
		}

		$limit = 50; // lets keep the reparsing conservative
		$processed_records = 0;
		while ($processed_records < $limit && $current > 0)
		{
			$start = max(1, $current + 1 - ($limit - $processed_records));
			$end   = max(1, $current);

			$reparser->reparse_range($start, $end);

			$processed_records = $end - $start + 1;
			$current = $start - 1;
		}

		return ($current === 0) ? true : $current;
	}
}