<?php

namespace Dope\ClassifierBundle\Services\Responses;

use Dope\ClassifierBundle\Services\Responses\PredictionResponse\Prediction;
use stdClass;

/**
 * @category  classifier
 * @copyright Copyright (c) 2017 CHECK24 Vergleichsportal Flüge GmbH
 */
class PredictionResponse
{
    /**
     * @var int
     */
    protected $code;

    /**
     * @var
     */
    protected $message;

    /**
     * @var string
     */
    protected $service;

    /**
     * @var float
     */
    protected $time;

    /**
     * @var \Dope\ClassifierBundle\Services\Responses\PredictionResponse\Prediction[]
     */
    protected $predictions;

    /**
     * PredictionResponse constructor.
     *
     * @param int $status
     * @param string $message
     * @param string $service
     * @param float $time
     * @param Prediction[] $predictions
     */
    public function __construct(
        int $status,
        string $message,
        string $service = null,
        float $time = null,
        array $predictions = []
    ) {
        $this->status = $status;
        $this->message = $message;
        $this->service = $service;
        $this->time = $time;
        $this->predictions = $predictions;
    }

    /**
     * @param stdClass $response
     * @return PredictionResponse
     */
    public static function buildResponse(stdClass $response)
    {
        if ($response->status->code != 200) {
            return new self($response->status->code, $response->status->msg);
        }
        $serviceName = $response->head->service;
        $runTime = $response->head->time;
        $predictions = [];
        foreach ($response->body->predictions[0]->classes as $class) {
            $predictions[] = new Prediction($class->prob == INF?1:$class->prob, $class->cat, $class->last??false);
        }
        return new self($response->status->code, $response->status->msg, $serviceName, $runTime, $predictions);
    }

    /**
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @return float
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return Prediction[]
     */
    public function getPredictions()
    {
        return $this->predictions;
    }

}
