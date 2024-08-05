<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Tree implements IteratorAggregate {
	const LEAVES_ONLY = 0;
	const PARENTS_FIRST = 1;
	const CHILDREN_FIRST = 2;
	protected $array;
	protected $show;
	public function __construct($array, $show = self::PARENTS_FIRST) {
		$this->array = $array;
		$this->show = $show;
	}
	public function getIterator(): Traversable {
		$array_iterator = new RecursiveArrayIterator($this->array);
		$mode = $this->show;
		switch ($this->show) {
			case self::LEAVES_ONLY:
				if (defined('RecursiveIteratorIterator::LEAVES_ONLY')) {
					$mode = RecursiveIteratorIterator::LEAVES_ONLY;
				} else {
					$mode = RIT_LEAVES_ONLY;
				}
				break;
			case self::PARENTS_FIRST:
				if (defined('RecursiveIteratorIterator::SELF_FIRST')) {
					$mode = RecursiveIteratorIterator::SELF_FIRST;
				} else {
					$mode = RIT_SELF_FIRST;
				}
				break;
			case self::CHILDREN_FIRST:
				if (defined('RecursiveIteratorIterator::CHILD_FIRST')) {
					$mode = RecursiveIteratorIterator::CHILD_FIRST;
				} else {
					$mode = RIT_CHILD_FIRST;
				}
				break;
		}
		return new RecursiveIteratorIterator($array_iterator, $mode);
	}
}
// TEST
//$structure = array(
//	'Item A (top)' => array(
//		'Item B (depth: 1)' => array(
//			'Item C (depth: 2)' => array(),
//			'Item D (depth: 2)' => array(
//				'Item E (depth: 3)' => array())
//		),
//		'Item X (depth: 1)' => array(
//			'Item Y (depth: 2)' => array(
//				'Item Z (depth: 3)' => array())
//		)
//	)
//);
//echo '<p><strong>Original array:</strong></p><pre>';
//var_dump($structure);
//echo '</pre>';
//$tree = new Tree($structure, Tree::CHILDREN_FIRST);
//echo '<p><strong>Children first:</strong><br />';
//foreach ($tree as $key => $value) {
//	echo $key, '<br />';
//}
//echo '</p>';
//$tree = new Tree($structure, Tree::PARENTS_FIRST);
//echo '<p><strong>Parents first:</strong><br />';
//foreach ($tree as $key => $value) {
//	echo $key, '<br />';
//}
//echo '</p>';
?>