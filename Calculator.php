<?php

/* 
 * Implemented and used algos for calulation:
 *  - https://en.wikipedia.org/wiki/Shunting-yard_algorithm
 *  - https://en.wikipedia.org/wiki/Reverse_Polish_notation
 *
 */

class Calculator
{
	/**
	 * @var array
	 */
	protected $tokens;

	/**
	 * @var array
	 */
	protected $ops = array(
		'+' => array('sequence' => 0),
		'-' => array('sequence' => 0),
		'*' => array('sequence' => 1),
		'/' => array('sequence' => 1),
	);

	/**
	 * Create tokens from the input string.
	  *
	 * @param string $strng
	 */
	protected function addInfixString($string)
	{
		$regex = '(([0-9]*\.[0-9]+|[0-9]+|\+|-|\*|/)|\s+)';
		$this->tokens = preg_split($regex, $string, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		$this->tokens = array_map('trim', $this->tokens);
	}

	/**
	 * Return an array of the token in reverse polish notation.
	 *
	 * @return array
	 */
	protected function getRPNTokens()
	{
		$stack  = new SplStack();
		$queue = new SplQueue();

		foreach ($this->tokens as $token)
		{
			if (is_numeric($token))
			{
				$queue->enqueue($token);
			} 
			elseif (isset($this->ops[$token])) 
			{
				while ($this->isOpOnTop($stack) && $this->hasLowerSequence($token, $stack->top()))
				{
					$queue->enqueue($stack->pop());
				}
				$stack->push($token);
			} 
			else 
			{
				throw new Exception('Invalid token: ' . $token);
			}
		}

		while ($this->isOpOnTop($stack)) 
		{
			$queue->enqueue($stack->pop());
		}

		if (count($stack) > 0)
		{
			throw new Exception('Misplaced number in input: ' . json_encode($this->tokens));
		}

		return iterator_to_array($queue);
	}

	/**
	 * Is the item at the top of an operator?
	 *
	 * @param SplStack $stack
	 * @return bool
	 */
	protected function isOpOnTop(SplStack $stack)
	{
		if (count($stack) == 0) 
		{
			return false;
		}

		$top = $stack->top();

		if (!isset($this->ops[$top])) 
		{
			return false;
		}
		return true;
	}

	/**
	 * Does the first operator have lower sequence than the second operator?
	 *
	 * @param string $op1
	 * @param string $op2
	 * @return bool
	 */
	protected function hasLowerSequence($op1, $op2)
	{
		return $this->ops[$op1]['sequence'] <= $this->ops[$op2]['sequence'];
	}

	/**
	 * Evaluate.
	 *
	 * @param string $input
	 * @return float
	 */
	public function evaluate($input)
	{
		$stack = new SplStack();
		$this->addInfixString($input);

		foreach ($this->getRPNTokens() as $token)
		{
			if (is_numeric($token)) 
			{
				$stack->push((float) $token);
				continue;
			}

			$num2 = $stack->pop();
			$num1 = $stack->pop();

			switch ($token) 
			{
				case '+':
					$stack->push($num1 + $num2);
				break;

				case '-':
					$stack->push($num1 - $num2);
				break;

				case '*':
					$stack->push($num1 * $num2);
				break;

				case '/':
					$stack->push($num1 / $num2);
				break;

				default:
					throw new Exception('Invalid operation: ' . $token);
				break;
			}
		}
		return $stack->top();
	}
}

$calc = new Calculator();
print($calc->evaluate('1 + 1 * 3 + 3').PHP_EOL);
