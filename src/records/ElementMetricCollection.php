<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/scorecard/license
 * @link       https://www.flipboxfactory.com/software/scorecard/
 */

namespace flipbox\scorecard\records;

use flipbox\ember\helpers\ArrayHelper;
use flipbox\scorecard\db\ElementMetricQuery;
use flipbox\scorecard\helpers\MetricHelper;
use flipbox\scorecard\metrics\MetricInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property ElementMetric[] $children
 */
abstract class ElementMetricCollection extends ElementMetric
{
    /**
     * @return array
     */
    const METRICS = [];

    /**
     * @var MetricInterface[]
     */
    private $metrics;


    /*******************************************
     * METRIC INTERFACE
     *******************************************/

    /**
     * @return float
     */
    protected function calculateScore(): float
    {
        // Sum of total/weight
        $total = $weights = 0;

        foreach ($this->getMetrics() as $metric) {
            $total += $metric->getScore();
            $weights += $metric->getWeight();
        }

        return $total > 0 ? (float)($total / $weights) : 0;
    }


    /*******************************************
     * METRICS (CHILDREN)
     *******************************************/

    /**
     * @return MetricInterface[]
     */
    public function getMetrics(): array
    {
        if ($this->metrics === null) {
            $this->setMetrics(
                $this->loadMetrics()
            );
        }

        return $this->metrics;
    }

    /**
     * @return array
     */
    protected function loadMetrics(): array
    {
        if ($this->getIsNewRecord()) {
            return (array)static::METRICS;
        }

        return array_filter(array_merge(
            $this->children,
            (array)ArrayHelper::getValue($this->settings, 'metrics', [])
        ));
    }

    /**
     * @param array $metrics
     * @return $this
     */
    public function setMetrics(array $metrics = [])
    {
        $this->metrics = [];

        foreach ($metrics as $metric) {
            if (!$metric instanceof MetricInterface) {
                $metric = $this->createMetric($metric);
            }

            $this->metrics[] = $metric;
        }

        return $this;
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @param $metric
     * @return MetricInterface
     */
    protected function createMetric($metric): MetricInterface
    {
        if (is_string($metric)) {
            $metric = ['class' => $metric];
        }

        /** Pass along other vars */
        if (is_array($metric)) {
            if (!isset($metric['elementId'])) {
                $metric['element'] = $metric['element'] ?? $this->getElement();
            }

            $metric['dateCalculated'] = $metric['dateCalculated'] ?? $this->dateCalculated;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return MetricHelper::create($metric);
    }

    /*******************************************
     * CHILDREN (RECORDS)
     *******************************************/

    /**
     * @return ElementMetricQuery
     */
    public function getChildren(): ElementMetricQuery
    {
        /** @var ElementMetricQuery $query */
        $query = $this->hasMany(
            static::class,
            ['parentId' => 'id']
        );

        // Children have parents
        $query->parentId(':notempty:');

        return $query;
    }

    /*******************************************
     * VALIDATE (CHILD RECORDS)
     *******************************************/

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        $success = true;
        foreach ($this->getMetrics() as $metric) {
            if ($metric instanceof ElementMetric) {
                if (!$metric->validate()) {
                    $success = false;
                }
            }
        }

        return $success ? parent::beforeValidate() : false;
    }

    /*******************************************
     * SAVE (CHILD RECORDS)
     *******************************************/

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        /** @var array $metrics */
        $metrics = [];

        foreach ($this->getMetrics() as $metric) {
            if (!$metric instanceof ElementMetric) {
                $metrics[] = $metric->toConfig();
            }
        }

        // Merge into settings
        if (!empty($metrics)) {
            $this->settings = array_filter(array_merge(
                (array)$this->settings,
                [
                    'metrics' => $metrics
                ]
            ));
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        /** @var ElementMetric[] $metrics */
        $children = [];

        foreach ($this->getMetrics() as $metric) {
            if ($metric instanceof ElementMetric) {
                $children[] = $metric;
            }
        }

        if (!empty($children)) {
            $success = true;
            foreach ($children as $child) {
                $child->parentId = $this->id;

                if (!$child->save()) {
                    $success = false;
                }
            }

            if (!$success) {
                $this->addError('children', 'Unable to save children');
            }
        }

        return parent::afterSave($insert, $changedAttributes);
    }
}
