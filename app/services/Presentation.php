<?php

namespace Echo511\RevealJs;

class Presentation extends \Nette\Object
{
	
	public function __construct($name, $session)
	{
		$this->name = $name;
		$this->session = $session;
	}

	/** @var $name Name of presentation */
	private $name;

	public function getName()
	{
		return $this->name;
	}

	/** @var $session Id of session */
	private $session;

	public function getHash()
	{
		return md5($this->name.$this->session);
	}

	public function getIndexFile($presentationDir)
	{
		return $presentationDir . '/' . $this->getName() . '/index.latte';
	}

	private $tokens = array();

	public function setTokens(array $tokens)
	{
		$this->tokens = $tokens;
	}

	public function getMasterTokens()
	{
		return $this->tokens;
	}

	public function getSlaveTokens()
	{
		$tokens = $this->tokens;
		$tokens['secret'] = null;
		return $tokens;
	}

}