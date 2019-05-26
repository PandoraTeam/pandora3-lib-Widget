<?php
namespace Pandora3\Libs\Widget\Exceptions;

use RuntimeException;
use Pandora3\Core\Interfaces\Exceptions\CoreException;
use Throwable;

/**
 * Class WidgetRenderException
 * @package Pandora3\Libs\Widget\Exceptions
 */
class WidgetRenderException extends RuntimeException implements CoreException {

	/**
	 * @param string $viewPath
	 * @param string $className
	 * @param null|Throwable $previous
	 */
	public function __construct(string $viewPath, string $className, ?Throwable $previous = null) {
		$message = "Rendering view '$viewPath' failed for [$className]";
		parent::__construct($message, E_WARNING, $previous);
	}

}