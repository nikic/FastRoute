<?php
declare(strict_types=1);

namespace FastRoute;

use ArrayAccess;

/**
 * Result Object
 */
class Result implements ArrayAccess
{
	public const NOT_FOUND = 0;
	public const FOUND = 1;
	public const METHOD_NOT_ALLOWED = 2;

	protected $matched = false;
	protected $route;
	protected $result = [];

	/**
	 * @param array $result Result
	 * @return $this
	 */
	public static function fromArray(array $result)
	{
		$self = new self();
		$self->result = $result;

		return $self;
	}

	/**
	 * @return bool
	 */
	public function handler()
	{
		if (!isset($this->result[1])) {
			return null;
		}

		return $this->result[1];
	}

	/**
	 * @return mixed
	 */
	public function args()
	{
		if (!isset($this->result[2])) {
			return [];
		}

		return $this->result[2];
	}

	/**
	 * @return bool
	 */
	public function routeMatched(): bool
	{
		return $this->result[0] === self::FOUND;
	}

	/**
	 * @return bool
	 */
	public function methodNotAllowed(): bool
	{
		return $this->result[0] === self::METHOD_NOT_ALLOWED;
	}

	/**
	 * @return bool
	 */
	public function routeNotFound(): bool
	{
		return $this->result[0] === self::NOT_FOUND;
	}

	/**
	 * @inheritDoc
	 */
	public function offsetExists($offset)
	{
		return isset($this->result[$offset]);
	}

	/**
	 * @inheritDoc
	 */
	public function offsetGet($offset)
	{
		return $this->result[$offset];
	}

	/**
	 * @inheritDoc
	 */
	public function offsetSet($offset, $value)
	{
		throw new \RuntimeException(
			'You ca\'t mutate the state of the result'
		);
	}

	/**
	 * @inheritDoc
	 */
	public function offsetUnset($offset)
	{
		throw new \RuntimeException(
			'You ca\'t mutate the state of the result'
		);
	}
}
