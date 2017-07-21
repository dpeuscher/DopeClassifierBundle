<?php

namespace Dope\ClassifierBundle\Services;

/**
 * @category  evernote
 * @copyright Copyright (c) 2017 CHECK24 Vergleichsportal Flüge GmbH
 */
class TestingService
{
    /**
     * @var ClassFileWriter
     */
    protected $classFileWriter;

    /**
     * @var PredictionService
     */
    protected $predictionService;

    /**
     * TestingService constructor.
     *
     * @param ClassFileWriter $classFileWriter
     * @param PredictionService $predictionService
     */
    public function __construct(ClassFileWriter $classFileWriter, PredictionService $predictionService)
    {
        $this->classFileWriter = $classFileWriter;
        $this->predictionService = $predictionService;
    }

    /**
     * @param string $detectionDatabase
     * @param string $specificDatabase
     * @return array
     */
    public function getAccuracy(string $detectionDatabase, string $specificDatabase)
    {
        $accuracyArray = [];
        foreach ($this->classFileWriter->getTestData($detectionDatabase) as list('path' => $path, 'class' => $class)) {
            $response = $this->predictionService->predictText($specificDatabase, trim(file_get_contents($path)),
                ['parameters' => ['output' => ['best' => 10]]]
            );
            foreach ($response->getPredictions() as $prediction) {
                if ($prediction->getClass() == $class) {
                    $accuracyArray[] = $prediction->getProbability();
                    continue 2;
                }
            }
            $accuracyArray[] = 0;
        }
        return $accuracyArray;
    }

    /**
     * @param string $detectionDatabase
     * @param string $specificDatabase
     * @return array|null
     */
    public function getAccuracyByCategory(string $detectionDatabase, string $specificDatabase)
    {
        $accuracyClassArray = [];
        foreach ($this->classFileWriter->getTestData($detectionDatabase) as list('path' => $path, 'class' => $class)) {
            $response = $this->predictionService->predictText($specificDatabase, trim(file_get_contents($path)),
                ['parameters' => ['output' => ['best' => 10]]]
            );
            foreach ($response->getPredictions() as $prediction) {
                if ($prediction->getClass() == $class) {
                    if (!isset($accuracyClassArray[$class])) {
                        $accuracyClassArray[$class] = [];
                    }
                    $accuracyClassArray[$class][] = $prediction->getProbability();
                    continue 2;
                }
            }
            $accuracyClassArray[$class][] = 0;
        }
        return $accuracyClassArray;
    }

    /**
     * @param $array
     * @param bool $flaten
     * @return array|float
     */
    public function calculateAverage($array, $flaten = false)
    {
        if ($flaten) {
            $array = $this->flatenArray($array);
        }
        $returnArray = [];
        $sum = 0;
        $count = 0;
        foreach ($array as $class => $subArray) {
            if (is_array($subArray)) {
                $returnArray[$class] = $this->calculateAverage($subArray, false);
            } else {
                $sum += $subArray;
                $count++;
            }
        }
        if (empty($returnArray)) {
            if (!$count) {
                return null;
            }
            return $sum / $count;
        }
        return $returnArray;
    }

    /**
     * @param $array
     * @return array
     */
    protected function flatenArray($array): array
    {
        $flatArray = [];
        foreach ($array as $subArray) {
            if (is_array($subArray)) {
                foreach ($this->flatenArray($subArray) as $single) {
                    $flatArray[] = $single;
                }
            } else {
                $flatArray[] = $subArray;
            }
        }
        return $flatArray;
    }
}
