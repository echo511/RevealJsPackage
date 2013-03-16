<?php

/**
 * Homepage presenter.
 * @TODO refactor
 */
class PresentPresenter extends BasePresenter
{

	/** @persistent Name of presentation */
	public $presentation;

	/** @persistent Ability for more speakers to run the same presentation using multiplex */
	public $session = null;

	/** @persistent Links presentations on separate devices - socket.io.js */
	public $useMultiplex = false;

	/** @persistent Should I control navigation in presentation */
	public $master = false;

	private $presentationDir;

	private $wwwDir;

	private $multiplex;

	private $cache;


	public function injectMultiplex(\Echo511\RevealJs\Multiplex $multiplex)
	{
		$this->multiplex = $multiplex;
	}

	public function injectCache(\Nette\Caching\IStorage $cacheStorage)
	{
		$this->cache = new \Nette\Caching\Cache($cacheStorage);
	}

	public function startup()
	{
		parent::startup();
		
		$parameters = $this->context->getParameters();
		$this->wwwDir = $parameters['wwwDir'];
		$this->presentationDir = $parameters['presentationDir'];
	}



	public function templatePrepareFilters($template)
	{
		$latte = $this->getPresenter()->getContext()->nette->createLatte();
		$template->registerFilter($latte);

		$set = Nette\Latte\Macros\MacroSet::install($latte->compiler);
		$set->addMacro('imagePath', 'echo $_presenter->getThumbnail(%node.array);', NULL);
	}



	public function beforeRender()
	{
		parent::beforeRender();

		if($this->presentation != '') {
			$this->setView('present');
		}
	}



	public function renderList()
	{
		$this->template->presentations = array();
		foreach(\Nette\Utils\Finder::findDirectories('*')->in($this->presentationDir . '/presentations') as $presentation) {
			$this->template->presentations[] = $presentation;
		}
	}



	public function renderPresent()
	{
		$presentation = new \Echo511\RevealJs\Presentation($this->presentation, $this->session);
		$this->template->setFile($presentation->getIndexFile($this->presentationDir . '/presentations'));

		if($this->useMultiplex) {
			$this->multiplex->startSession($presentation);

			if($this->master)
				$tokens = $presentation->getMasterTokens();
			else
				$tokens = $presentation->getSlaveTokens();

			$this->template->multiplexId = $tokens['id'];
			$this->template->multiplexSecret = $tokens['secret'];
		}
	}



	public function getThumbnail($basename, $width = NULL, $height = NULL, $quality = 85)
	{

		if(is_array($basename)) {
			$width = array_key_exists('width', $basename) ? $basename['width'] : null;
			$height = array_key_exists('height', $basename) ? $basename['height'] : null;
			$quality = array_key_exists('quality', $basename) ? $basename['quality'] : null;
			$basename = $basename[0];
		}

		if ($width === NULL && $height === NULL)
			$width = 100;

		$filename = $this->presentationDir . '/presentations/' . $this->presentation . '/images/' . $basename;

		// broken image
		if (!file_exists($filename))
			return '';

		// create hash that specifies the path and the settings
		$info = pathinfo($filename);
		$ext = $info['extension'];
		$hash = md5($filename) . '_' . $width . '_' . $height . '_' . $quality;
		
		$tempFilename = $this->wwwDir . '/temp/' . $this->presentation . '/' . $hash . '.' . $ext;
		$publicSrc = $this->template->basePath . '/temp/' . $this->presentation . '/' . $hash . '.' . $ext;

		// is the file cached?
		if (file_exists($tempFilename))
			return $publicSrc;

		// create new file
		$image = \Nette\Image::fromFile($filename);

		// calculate missing dimensions to keep proportions
		if ($width === NULL)
			$width = ($image->getWidth() / $image->getHeight()) * $height;
		if ($height === NULL)
			$height = ($image->getHeight() / $image->getWidth()) * $width;

		$tempDir = dirname($tempFilename);
		if(!is_dir($tempDir))
			mkdir($tempDir);

		// save
		$image->resize($width, $height, \Nette\Image::FILL)
		       ->crop('50%', '50%', $width, $height)
		       ->save($tempFilename, $quality);

		// it is cached now
		return $publicSrc;
   }

   public function renderCleanCache()
   {
   		$this->cache->clean(array(
   			\Nette\Caching\Cache::ALL => true,
   		));
   		echo 'Hotovo!';
   		$this->terminate();
   }

}
