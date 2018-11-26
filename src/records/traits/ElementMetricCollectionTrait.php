<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/scorecard/license
 * @link       https://www.flipboxfactory.com/software/scorecard/
 */

namespace flipbox\scorecard\records\traits;

use flipbox\ember\helpers\ArrayHelper;
use flipbox\scorecard\db\ElementMetricQuery;
use flipbox\scorecard\helpers\MetricHelper;
use flipbox\scorecard\metrics\MetricInterface;
use flipbox\scorecard\records\ElementMetric;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property ElementMetric[] $children
 */
trait ElementMetricCollectionTrait
{
    /**
     * @var MetricInterface[]
     */
    private $metrics;

    /**
     * @return array
     */
    abstract protected function newMetrics(): array;

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

        return (float)($total / $weights);
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
            return $this->newMetrics();
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

        /** Pass along the element */
        if (is_array($metric)) {
            $metric = $this->prepareMetricConfig($metric);
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return MetricHelper::create($metric);
    }

    /**
     * Prepare the metric config for new creation
     *
     * @param array $config
     * @return array
     */
    protected function prepareMetricConfig(array $config = []): array
    {
        /** Pass along the element */
        if (!isset($config['elementId']) || !isset($config['element'])) {
            $config['element'] = $this->getElement();
        }

        return $config;
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
    protected function validateMetrics(): bool
    {
        $success = true;
        foreach ($this->getMetrics() as $metric) {
            if ($metric instanceof ElementMetric) {
                if (!$metric->validate()) {
                    $success = false;
                }
            }
        }

        return $success;
    }

    /*******************************************
     * SAVE (CHILD RECORDS)
     *******************************************/

    /**
     * @return static
     */
    protected function addMetricsToSettings()
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

        return $this;
    }

    /**
     * @return static
     */
    protected function saveChildRecords()
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

        return $this;
    }
}
