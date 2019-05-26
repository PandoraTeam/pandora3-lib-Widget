<?php
namespace Pandora3\Libs\Widget;

use Pandora3\Core\Container\Container;
use Pandora3\Core\Debug\Debug;
use Pandora3\Core\Interfaces\RendererInterface;
use Pandora3\Libs\Widget\Exceptions\WidgetRenderException;
use Pandora3\Plugins\Twig\TwigRenderer;

/**
 * Class Widget
 * @package Pandora3\Libs\Widget
 *
 * @property-read array $context
 */
abstract class Widget {

	/** @var Container $container */
	protected $container;

	/** @var array $context */
	protected $context;

	/** @var string $path */
	protected $path;

	/** @var array $scripts */
	protected static $scripts = [];

	/**
	 * @param array $context
	 */
	public function __construct($context = []) {
		$this->context = $context;
		$this->path = $this->getPath();
		$this->container = new Container;
		$this->dependencies($this->container);
	}

	/**
	 * @param Container $container
	 */
	protected function dependencies(Container $container): void {
		$container->set(RendererInterface::class, TwigRenderer::class);
		$container->setShared(TwigRenderer::class, function() {
			return new TwigRenderer(APP_PATH.'/Views');
		});
	}

	/**
	 * @return array
	 */
	protected function getContext(): array {
		return $this->context;
	}

	/**
	 * @return string
	 */
	private function getPath(): string {
		$reflection = new \ReflectionClass(static::class);
		return dirname($reflection->getFileName());
	}

	/**
 	 * @ignore
	 * @param string $property
	 * @return mixed
	 */
	public function __get(string $property) {
		if ($property === 'context') {
			return $this->getContext();
		}
		$className = static::class;
		Debug::logException(new \Exception("Undefined property '$property' for [$className]", E_NOTICE));
		return null;
	}

	/**
	 * @return string
	 */
	abstract protected function getView(): string;

	/**
	 * Preparing context to render
	 * @param array $context
	 * @return array
	 */
	protected function beforeRender(array $context): array {
		return $context;
	}

	/**
	 * @param array $context
	 * @return string
	 * @throws WidgetRenderException
	 */
	public function render(array $context = []): string {
		/** @var RendererInterface $renderer */
		$renderer = $this->container->get(RendererInterface::class);
		$viewPath = $this->getView();
		$context = $this->beforeRender( array_replace(
			$this->getContext(), ['widget' => $this], $context
		) );
		try {
			return $renderer->render($viewPath, $context);
		} catch (\RuntimeException $ex) {
			throw new WidgetRenderException($viewPath, static::class, $ex);
		}
	}

	/**
	 * @param string $filename
	 * @return string
	 */
	public function addScript(string $filename): string {
		$filename = "{$this->path}/{$filename}";
		if (array_key_exists($filename, self::$scripts)) {
			return '';
		}
		self::$scripts[$filename] = true;
		if (!is_file($filename)) {
			Debug::logException(new \Exception("File '$filename' not found", E_WARNING));
			return '';
		}
		ob_start();
			echo '<script>';
				echo file_get_contents($filename);
			echo '</script>';
		return ob_get_clean();
	}

}