<?php

namespace Dope\ClassifierBundle\Services\Responses\PredictionResponse;

/**
 * @category  classifier
 * @copyright Copyright (c) 2017 CHECK24 Vergleichsportal Flüge GmbH
 */
class Prediction
{
    /**
     * @var float
     */
    protected $probability;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var boolean
     */
    protected $last;

    /**
     * Prediction constructor.
     *
     * @param float $probability
     * @param string $class
     * @param bool $last
     */
    public function __construct($probability, $class, $last)
    {
        $this->probability = $probability;
        $this->class = $class;
        $this->last = $last;
    }

    /**
     * @return float
     */
    public function getProbability()
    {
        return $this->probability;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return bool
     */
    public function getLast()
    {
        return $this->last;
    }

    public function isLast()
    {
        return $this->last;
    }

}
