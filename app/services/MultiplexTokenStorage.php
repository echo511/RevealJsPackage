<?php

namespace Echo511\RevealJs;

class MultiplexTokenStorage extends \Nette\Object implements IMultiplexTokenStorage
{

	/** @var \Nette\Caching\Cache */
	private $cache;

	public function __construct(\Nette\Caching\IStorage $cacheStorage)
	{
		$this->cache = new \Nette\Caching\Cache($cacheStorage, 'Echo511.RevealJs.Multiplex');
	}

	public function save(Presentation $presentation, array $tokens)
	{
		$this->cache->save($presentation->getHash(), $tokens);
	}

	public function load(Presentation $presentation)
	{
		return $this->cache->load($presentation->getHash());
	}

}