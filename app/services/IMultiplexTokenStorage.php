<?php

namespace Echo511\RevealJs;

interface IMultiplexTokenStorage
{

	public function save(Presentation $presentation, array $tokens);

	public function load(Presentation $presentation);
}