<?php

class MorphyUtils
{
	static $_morphy_instance = false;

	public static function init()
	{
		if (isset(self::$_morphy_instance) && is_object(self::$_morphy_instance)) return true;
		//require_once __DIR__ . '/src/common.php';
		self::$_morphy_instance = new phpMorphy(__DIR__ . '/dicts/', 'ru_RU', array('storage' => PHPMORPHY_STORAGE_FILE));
		return true;
	}
	public static function getRoots($word)
	{
		$roots = self::getRoot($word);
		if (!is_array($roots)) {
			$roots = array($roots);
		}
		return $roots;
	}
	public static function getRoot($word)
	{
		self::init();
		$pseudo_roots = self::$_morphy_instance->getPseudoRoot(mb_strtoupper($word, 'UTF-8'), phpMorphy::IGNORE_PREDICT);
		self::$_morphy_instance = null;
		return $pseudo_roots;
	}
	public static function getRootOne($word)
	{
		self::init();
		$pseudo_roots = self::$_morphy_instance->getPseudoRoot(mb_strtoupper($word, 'UTF-8'), phpMorphy::IGNORE_PREDICT);
		self::$_morphy_instance = null;
		return is_array($pseudo_roots) ? $pseudo_roots[0] : $pseudo_roots;
	}
	public static function getVariants($word)
	{
		self::init();
		$all = self::$_morphy_instance->getAllForms(mb_strtoupper($word, 'UTF-8'), phpMorphy::IGNORE_PREDICT);
		self::$_morphy_instance = null;
		return $all;
	}
}