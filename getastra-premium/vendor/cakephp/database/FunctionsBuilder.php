<?php

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         3.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace AstraPrefixed\Cake\Database;

use AstraPrefixed\Cake\Database\Expression\FunctionExpression;
/**
 * Contains methods related to generating FunctionExpression objects
 * with most commonly used SQL functions.
 * This acts as a factory for FunctionExpression objects.
 */
class FunctionsBuilder
{
    /**
     * Returns a new instance of a FunctionExpression. This is used for generating
     * arbitrary function calls in the final SQL string.
     *
     * @param string $name the name of the SQL function to constructed
     * @param array $params list of params to be passed to the function
     * @param array $types list of types for each function param
     * @param string $return The return type of the function expression
     * @return \Cake\Database\Expression\FunctionExpression
     */
    protected function _build($name, $params = [], $types = [], $return = 'string')
    {
        return new FunctionExpression($name, $params, $types, $return);
    }
    /**
     * Helper function to build a function expression that only takes one literal
     * argument.
     *
     * @param string $name name of the function to build
     * @param string|\Cake\Database\ExpressionInterface $expression the function argument
     * @param array $types list of types to bind to the arguments
     * @param string $return The return type for the function
     * @return \Cake\Database\Expression\FunctionExpression
     */
    protected function _literalArgumentFunction($name, $expression, $types = [], $return = 'string')
    {
        if (!\is_string($expression)) {
            $expression = [$expression];
        } else {
            $expression = [$expression => 'literal'];
        }
        return $this->_build($name, $expression, $types, $return);
    }
    /**
     * Returns a FunctionExpression representing a call to SQL RAND function.
     *
     * @return \Cake\Database\Expression\FunctionExpression
     */
    public function rand()
    {
        return $this->_build('RAND', [], [], 'float');
    }
    /**
     * Returns a FunctionExpression representing a call to SQL SUM function.
     *
     * @param string|\Cake\Database\ExpressionInterface $expression the function argument
     * @param array $types list of types to bind to the arguments
     * @return \Cake\Database\Expression\FunctionExpression
     */
    public function sum($expression, $types = [])
    {
        $returnType = 'float';
        if (\current($types) === 'integer') {
            $returnType = 'integer';
        }
        return $this->_literalArgumentFunction('SUM', $expression, $types, $returnType);
    }
    /**
     * Returns a FunctionExpression representing a call to SQL AVG function.
     *
     * @param string|\Cake\Database\ExpressionInterface $expression the function argument
     * @param array $types list of types to bind to the arguments
     * @return \Cake\Database\Expression\FunctionExpression
     */
    public function avg($expression, $types = [])
    {
        return $this->_literalArgumentFunction('AVG', $expression, $types, 'float');
    }
    /**
     * Returns a FunctionExpression representing a call to SQL MAX function.
     *
     * @param string|\Cake\Database\ExpressionInterface $expression the function argument
     * @param array $types list of types to bind to the arguments
     * @return \Cake\Database\Expression\FunctionExpression
     */
    public function max($expression, $types = [])
    {
        return $this->_literalArgumentFunction('MAX', $expression, $types, \current($types) ?: 'string');
    }
    /**
     * Returns a FunctionExpression representing a call to SQL MIN function.
     *
     * @param string|\Cake\Database\ExpressionInterface $expression the function argument
     * @param array $types list of types to bind to the arguments
     * @return \Cake\Database\Expression\FunctionExpression
     */
    public function min($expression, $types = [])
    {
        return $this->_literalArgumentFunction('MIN', $expression, $types, \current($types) ?: 'string');
    }
    /**
     * Returns a FunctionExpression representing a call to SQL COUNT function.
     *
     * @param string|\Cake\Database\ExpressionInterface $expression the function argument
     * @param array $types list of types to bind to the arguments
     * @return \Cake\Database\Expression\FunctionExpression
     */
    public function count($expression, $types = [])
    {
        return $this->_literalArgumentFunction('COUNT', $expression, $types, 'integer');
    }
    /**
     * Returns a FunctionExpression representing a string concatenation
     *
     * @param array $args List of strings or expressions to concatenate
     * @param array $types list of types to bind to the arguments
     * @return \Cake\Database\Expression\FunctionExpression
     */
    public function concat($args, $types = [])
    {
        return $this->_build('CONCAT', $args, $types, 'string');
    }
    /**
     * Returns a FunctionExpression representing a call to SQL COALESCE function.
     *
     * @param array $args List of expressions to evaluate as function parameters
     * @param array $types list of types to bind to the arguments
     * @return \Cake\Database\Expression\FunctionExpression
     */
    public function coalesce($args, $types = [])
    {
        return $this->_build('COALESCE', $args, $types, \current($types) ?: 'string');
    }
    /**
     * Returns a FunctionExpression representing a call to SQL CAST function.
     *
     * @param string|\Cake\Database\ExpressionInterface $field Field or expression to cast.
     * @param string $type The target data type
     * @return \Cake\Database\Expression\FunctionExpression
     */
    public function cast($field, $type = '')
    {
        if (\is_array($field)) {
            deprecationWarning('Build cast function by FunctionsBuilder::cast(array $args) is deprecated. ' . 'Use FunctionsBuilder::cast($field, string $type) instead.');
            return $this->_build('CAST', $field);
        }
        $expression = $this->_literalArgumentFunction('CAST', $field);
        $expression->setConjunction(' AS')->add([$type => 'literal']);
        return $expression;
    }
    /**
     * Returns a FunctionExpression representing the difference in days between
     * two dates.
     *
     * @param array $args List of expressions to obtain the difference in days.
     * @param array $types list of types to bind to the arguments
     * @return \Cake\Database\Expression\FunctionExpression
     */
    public function dateDiff($args, $types = [])
    {
        return $this->_build('DATEDIFF', $args, $types, 'integer');
    }
    /**
     * Returns the specified date part from the SQL expression.
     *
     * @param string $part Part of the date to return.
     * @param string|\Cake\Database\ExpressionInterface $expression Expression to obtain the date part from.
     * @param array $types list of types to bind to the arguments
     * @return \Cake\Database\Expression\FunctionExpression
     */
    public function datePart($part, $expression, $types = [])
    {
        return $this->extract($part, $expression, $types);
    }
    /**
     * Returns the specified date part from the SQL expression.
     *
     * @param string $part Part of the date to return.
     * @param string|\Cake\Database\ExpressionInterface $expression Expression to obtain the date part from.
     * @param array $types list of types to bind to the arguments
     * @return \Cake\Database\Expression\FunctionExpression
     */
    public function extract($part, $expression, $types = [])
    {
        $expression = $this->_literalArgumentFunction('EXTRACT', $expression, $types, 'integer');
        $expression->setConjunction(' FROM')->add([$part => 'literal'], [], \true);
        return $expression;
    }
    /**
     * Add the time unit to the date expression
     *
     * @param string|\Cake\Database\ExpressionInterface $expression Expression to obtain the date part from.
     * @param string|int $value Value to be added. Use negative to subtract.
     * @param string $unit Unit of the value e.g. hour or day.
     * @param array $types list of types to bind to the arguments
     * @return \Cake\Database\Expression\FunctionExpression
     */
    public function dateAdd($expression, $value, $unit, $types = [])
    {
        if (!\is_numeric($value)) {
            $value = 0;
        }
        $interval = $value . ' ' . $unit;
        $expression = $this->_literalArgumentFunction('DATE_ADD', $expression, $types, 'datetime');
        $expression->setConjunction(', INTERVAL')->add([$interval => 'literal']);
        return $expression;
    }
    /**
     * Returns a FunctionExpression representing a call to SQL WEEKDAY function.
     * 1 - Sunday, 2 - Monday, 3 - Tuesday...
     *
     * @param string|\Cake\Database\ExpressionInterface $expression the function argument
     * @param array $types list of types to bind to the arguments
     * @return \Cake\Database\Expression\FunctionExpression
     */
    public function dayOfWeek($expression, $types = [])
    {
        return $this->_literalArgumentFunction('DAYOFWEEK', $expression, $types, 'integer');
    }
    /**
     * Returns a FunctionExpression representing a call to SQL WEEKDAY function.
     * 1 - Sunday, 2 - Monday, 3 - Tuesday...
     *
     * @param string|\Cake\Database\ExpressionInterface $expression the function argument
     * @param array $types list of types to bind to the arguments
     * @return \Cake\Database\Expression\FunctionExpression
     */
    public function weekday($expression, $types = [])
    {
        return $this->dayOfWeek($expression, $types);
    }
    /**
     * Returns a FunctionExpression representing a call that will return the current
     * date and time. By default it returns both date and time, but you can also
     * make it generate only the date or only the time.
     *
     * @param string $type (datetime|date|time)
     * @return \Cake\Database\Expression\FunctionExpression
     */
    public function now($type = 'datetime')
    {
        if ($type === 'datetime') {
            return $this->_build('NOW')->setReturnType('datetime');
        }
        if ($type === 'date') {
            return $this->_build('CURRENT_DATE')->setReturnType('date');
        }
        if ($type === 'time') {
            return $this->_build('CURRENT_TIME')->setReturnType('time');
        }
    }
    /**
     * Magic method dispatcher to create custom SQL function calls
     *
     * @param string $name the SQL function name to construct
     * @param array $args list with up to 3 arguments, first one being an array with
     * parameters for the SQL function, the second one a list of types to bind to those
     * params, and the third one the return type of the function
     * @return \Cake\Database\Expression\FunctionExpression
     */
    public function __call($name, $args)
    {
        switch (\count($args)) {
            case 0:
                return $this->_build($name);
            case 1:
                return $this->_build($name, $args[0]);
            case 2:
                return $this->_build($name, $args[0], $args[1]);
            default:
                return $this->_build($name, $args[0], $args[1], $args[2]);
        }
    }
}
