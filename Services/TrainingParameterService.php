<?php

namespace Dope\ClassifierBundle\Services;

use Dope\ClassifierBundle\Entity\TrainParameterClassResult;
use Dope\ClassifierBundle\Entity\TrainParameterResult;
use Dope\ClassifierBundle\Repository\TrainParameterClassResultRepository;
use Dope\ClassifierBundle\Repository\TrainParameterResultRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * @category  evernote
 * @copyright Copyright (c) 2017 CHECK24 Vergleichsportal Flüge GmbH
 */
class TrainingParameterService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const VERSION = 1;

    /**
     * @var ClassFileWriter
     */
    protected $classFileWriter;

    /**
     * @var TestingService
     */
    protected $testingService;

    /**
     * @var BuildDetectionDatabaseService
     */
    protected $buildDetectionDatabaseService;

    /**
     * @var TrainParameterResultRepository
     */
    protected $trainParameterResultRepository;

    /**
     * @var TrainParameterClassResultRepository
     */
    protected $trainParameterResultClassRepository;

    /**
     * TrainingParameterService constructor.
     *
     * @param ClassFileWriter $classFileWriter
     * @param TestingService $testingService
     * @param BuildDetectionDatabaseService $buildDetectionDatabaseService
     * @param TrainParameterResultRepository $trainParameterResultRepository
     * @param TrainParameterClassResultRepository $trainParameterResultClassRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClassFileWriter $classFileWriter,
        TestingService $testingService,
        BuildDetectionDatabaseService $buildDetectionDatabaseService,
        TrainParameterResultRepository $trainParameterResultRepository,
        TrainParameterClassResultRepository $trainParameterResultClassRepository,
        LoggerInterface $logger
    ) {
        $this->classFileWriter = $classFileWriter;
        $this->testingService = $testingService;
        $this->buildDetectionDatabaseService = $buildDetectionDatabaseService;
        $this->trainParameterResultRepository = $trainParameterResultRepository;
        $this->trainParameterResultClassRepository = $trainParameterResultClassRepository;
        $this->logger = $logger;
    }

    public function training(
        $detectionDatabase,
        $stepIterations,
        $stepTestInterval,
        $stepBaseLr,
        $stepBatchSize,
        $stepLayers
    ) {
        foreach ($stepIterations as $iterations) {
            foreach ($stepTestInterval as $testInterval) {
                foreach ($stepBaseLr as $baseLr) {
                    foreach ($stepBatchSize as $batchSize) {
                        foreach ($stepLayers as $layers) {
                            $layers = (array)$layers;
                            $customConfig = null;

                            $trainParameterResult = $this->trainParameterResultRepository->findOneBy([
                                'dbName'       => $detectionDatabase,
                                'iterations'   => $iterations,
                                'testInterval' => $testInterval,
                                'baseLr'       => $baseLr,
                                'batchSize'    => $batchSize,
                                'layers'       => json_encode($layers),
                                'version'      => static::VERSION,
                                'customConfig' => $customConfig,
                            ]);

                            if (!isset($trainParameterResult)) {
                                $trainParameterResult = new TrainParameterResult();
                                $trainParameterResult
                                    ->setDbName($detectionDatabase)
                                    ->setIterations($iterations)
                                    ->setTestInterval($testInterval)
                                    ->setBaseLr($baseLr)
                                    ->setBatchSize($batchSize)
                                    ->setLayers(json_encode($layers))
                                    ->setVersion(static::VERSION)
                                    ->setCustomConfig($customConfig);
                            }

                            $specificDatabase =
                                $detectionDatabase . '-' .
                                $this->align($iterations, $stepIterations, '0') . '-' .
                                $this->align($testInterval, $stepTestInterval, '0') . '-' .
                                $this->align($baseLr, $stepBaseLr, '0') . '-' .
                                $this->align($batchSize, $stepBatchSize, '0') . '-' .
                                $this->align($layers, $stepLayers, '-');

                            if (is_null($trainParameterResult->getId())) {
                                list(
                                    'dedeAccuracy' => $dedeAccuracy,
                                    'accuracyByCategory' => $accuracyByCategory,
                                    'runtime' => $runtime
                                    ) = $this->testRun($detectionDatabase, $specificDatabase, $iterations,
                                    $testInterval, $baseLr, $batchSize, $layers);

                                $testAccuracy = $this->testingService->calculateAverage($accuracyByCategory, true);

                                foreach ($accuracyByCategory as $category => $precisions) {
                                    $trainParameterClassResult = new TrainParameterClassResult();
                                    $trainParameterClassResult
                                        ->setTrainParameterResult($trainParameterResult)
                                        ->setClassName($category)
                                        ->setCount(count($precisions))
                                        ->setAccuracy($this->testingService->calculateAverage($precisions));
                                    $trainParameterResult->addClassResult($trainParameterClassResult);
                                }

                                $trainParameterResult->setOwnAccuracy($testAccuracy);
                                $trainParameterResult->setDedeAccuracy($dedeAccuracy);
                                $trainParameterResult->setRuntime($runtime);

                                $this->trainParameterResultRepository->add($trainParameterResult);
                            }


                            $this->logger->info($specificDatabase . ': ' .
                                'dedeAcc=' . number_format($trainParameterResult->getDedeAccuracy(), 5) . ' ' .
                                'testAcc=' . number_format($trainParameterResult->getOwnAccuracy(), 5) . ' ' .
                                'time=' . str_pad(number_format($trainParameterResult->getRuntime(), 1), 6, ' ', STR_PAD_LEFT)
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string $detectionDatabase
     * @param string $specificDatabase
     * @param int $iterations
     * @param int $testInterval
     * @param float $baseLr
     * @param int $batchSize
     * @param int[] $layers
     * @return array
     */
    protected function testRun(
        $detectionDatabase,
        $specificDatabase,
        $iterations,
        $testInterval,
        $baseLr,
        $batchSize,
        $layers
    ): array {
        $createConfig = [
            'parameters' => [
                'mllib' => [
                    'nclasses' => count(glob($this->classFileWriter->getWriteFolder() . '/' . $detectionDatabase . '/data/*')),
                    'layers'   => $layers,
                ],
            ],
            'model'      => [
                'repository' => $this->buildDetectionDatabaseService->getRepositoryLocation() . '/' . $specificDatabase,
            ],
        ];
        $config = [
            'parameters' => [
                'mllib' => [
                    'gpu'    => true,
                    'solver' => [
                        'iterations'    => $iterations,
                        'test_interval' => $testInterval,
                        'base_lr'       => $baseLr,
                    ],
                    'net'    => ['batch_size' => $batchSize,],
                ],
            ],
            'data'       => [$this->buildDetectionDatabaseService->getRepositoryLocation() . '/' . $detectionDatabase . '/data'],
        ];

        if (!is_dir($this->classFileWriter->getWriteFolder() . '/' . $specificDatabase)) {
            mkdir($this->classFileWriter->getWriteFolder() . '/' . $specificDatabase);
        }

        $before = microtime(true);

        $this->buildDetectionDatabaseService->createNewTextDatabase($specificDatabase,
            "iterations = " . $iterations . ', testInterval = ' . $testInterval . ', ' .
            "baseLr = " . $baseLr . ', batchSize = ' . $batchSize, $createConfig);

        $dedeAccuracy = $this->buildDetectionDatabaseService->trainTextDatabaseFromConfig($specificDatabase,
            $config);
        $accuracyByCategory = $this->testingService->getAccuracyByCategory($detectionDatabase,
            $specificDatabase);
        $runtime = microtime(true) - $before;

        return [
            'dedeAccuracy'       => $dedeAccuracy,
            'accuracyByCategory' => $accuracyByCategory,
            'runtime'            => $runtime,
        ];
    }

    /**
     * @param mixed $value
     * @param mixed[] $compare
     * @param string $string
     * @return string
     */
    private function align(
        $value,
        $compare,
        $string
    ) {
        $maxLen = 0;
        if (is_array($value) || is_object($value)) {
            $value = implode('.',(array)$value);
        }
        foreach ($compare as $item) {
            if (is_array($item) || is_object($item)) {
                $item = implode('.', (array)$item);
            }
            $maxLen = max(strlen($item), $maxLen);
        }
        return str_pad($value, $maxLen, $string, STR_PAD_LEFT);
    }
}