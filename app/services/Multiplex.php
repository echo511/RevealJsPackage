<?php

namespace Echo511\RevealJs;

class Multiplex extends \Nette\Object
{

	/** @var IMultiplexTokenStorage */
	private $tokenStorage;

	public function __construct(IMultiplexTokenStorage $tokenStorage)
	{
		$this->tokenStorage = $tokenStorage;
	}


	public function retrieveNewTokens()
	{
		$tokens = json_decode(file_get_contents('http://revealjs.jit.su/token'));
		return array(
			'id' => $tokens->socketId,
			'secret' => $tokens->secret,
		);
	}


	public function startSession(Presentation $presentation)
	{
		$tokens = $this->tokenStorage->load($presentation);
		
		if($tokens === null) {
			$tokens = $this->retrieveNewTokens();
			$this->tokenStorage->save($presentation, $tokens);
		}

		$presentation->setTokens($tokens);
		return $this;
	}

}