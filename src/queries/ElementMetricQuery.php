<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/scorecard/license
 * @link       https://www.flipboxfactory.com/software/scorecard/
 */

namespace flipbox\craft\scorecard\queries;

use craft\helpers\Db;
use flipbox\ember\db\CacheableActiveQuery;
use flipbox\ember\db\traits\AuditAttributes;
use flipbox\ember\db\traits\ElementAttribute;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ElementMetricQuery extends CacheableActiveQuery
{
    use ElementAttribute,
        AuditAttributes;

    /**
     * @var int|int[]|string|string[]|null
     */
    public $id;

    /**
     * @var int|int[]|string|string[]|null
     */
    public $parentId = ':empty:';

    /**
     * @var float|float[]|string|string[]|null
     */
    public $score;

    /**
     * @var float|float[]|string|string[]|null
     */
    public $weight;

    /**
     * @var string|string[]|null
     */
    public $version;

    /**
     * @var string|string[]|null
     */
    public $class;

    /**
     * @var mixed
     */
    public $dateCalculated;

    /**
     * @var array
     */
    public $orderBy = [
        'dateCalculated' => SORT_DESC
    ];


    /*******************************************
     * ATTRIBUTES
     *******************************************/

    /**
     * @param int|int[]|string|string[]|null $id
     * @return $this
     */
    public function id($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param int|int[]|string|string[]|null $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->id($id);
    }

    /**
     * @param int|int[]|string|string[]|null $id
     * @return $this
     */
    public function parentId($id)
    {
        $this->parentId = $id;
        return $this;
    }

    /**
     * @param int|int[]|string|string[]|null $id
     * @return $this
     */
    public function setParentId($id)
    {
        return $this->parentId($id);
    }

    /**
     * @param float|float[]|string|string[]|null $score
     * @return $this
     */
    public function score($score)
    {
        $this->score = $score;
        return $this;
    }

    /**
     * @param float|float[]|string|string[]|null $score
     * @return $this
     */
    public function setScore($score)
    {
        return $this->score($score);
    }

    /**
     * @param float|float[]|string|string[]|null $weight
     * @return $this
     */
    public function weight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * @param float|float[]|string|string[]|null $weight
     * @return $this
     */
    public function setWeight($weight)
    {
        return $this->weight($weight);
    }

    /**
     * @param string|string[]|null $version
     * @return $this
     */
    public function version($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @param string|string[]|null $version
     * @return $this
     */
    public function setVersion($version)
    {
        return $this->version($version);
    }

    /**
     * @param string|string[]|null $class
     * @return $this
     */
    public function class($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @param string|string[]|null $class
     * @return $this
     */
    public function setClass($class)
    {
        return $this->class($class);
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function dateCalculated($value)
    {
        $this->dateCalculated = $value;
        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setDateCalculated($value)
    {
        return $this->dateCalculated($value);
    }

    /*******************************************
     * PREPARE
     *******************************************/

    /**
     * @inheritdoc
     */
    public function prepare($builder)
    {
        // Apply attribute params
        $this->prepareParams();
        $this->applyAuditAttributeConditions();

        return parent::prepare($builder);
    }

    /*******************************************
     * PREPARE PARAMS
     *******************************************/

    /**
     * Apply environment params
     */
    protected function prepareParams()
    {
        $attributes = ['id', 'parentId', 'score', 'weight', 'version', 'class'];

        foreach ($attributes as $attribute) {
            if (($value = $this->{$attribute}) !== null) {
                $this->andWhere(Db::parseParam($attribute, $value));
            }
        }

        if (($value = $this->element) !== null) {
            $this->andWhere(Db::parseParam('elementId', $value));
        }

        if ($this->dateCalculated !== null) {
            $this->andWhere(Db::parseDateParam('dateCalculated', $this->dateCalculated));
        }
    }
}
