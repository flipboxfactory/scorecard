<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/scorecard/license
 * @link       https://www.flipboxfactory.com/software/scorecard/
 */

namespace flipbox\craft\scorecard\metrics;

use craft\helpers\StringHelper;
use yii\base\BaseObject;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
abstract class AbstractMetric extends BaseObject implements MetricInterface
{
    /**
     * @var float
     */
    public $weight = 1;

    /**
     * @var string
     */
    public $version = '1.0.0';

    /**
     * @var float
     */
    private $score;

    /**
     * @return float
     */
    abstract protected function calculateScore(): float;

    /**
     * @inheritdoc
     * @throws \ReflectionException
     */
    public static function displayName(): string
    {
        return StringHelper::titleize(
            (new \ReflectionClass(static::class))
                ->getShortName()
        );
    }

    /**
     * @inheritdoc
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @inheritdoc
     */
    public function getWeight(): float
    {
        return $this->weight;
    }

    /**
     * @return float
     */
    public function getScore(): float
    {
        if ($this->score === null) {
            $this->score = $this->calculateScore();
        }

        return $this->score * $this->getWeight();
    }

    /**
     * @return $this
     */
    public function resetScore()
    {
        $this->score = null;
        return $this;
    }

    /**
     * @param float|null $score
     * @return $this
     */
    public function setScore(float $score = null)
    {
        $this->score = $score;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toConfig(): array
    {
        return [
            'class' => static::class,
            'weight' => $this->getWeight(),
            'version' => $this->getVersion(),
            'score' => $this->getScore()
        ];
    }
}
