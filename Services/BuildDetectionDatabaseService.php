<?php

namespace Dope\ClassifierBundle\Services;

use Exception;
use Unirest\Request;

/**
 * @category  classifier
 * @copyright Copyright (c) 2017 CHECK24 Vergleichsportal Flüge GmbH
 */
class BuildDetectionDatabaseService extends AbstractDeepDetectService
{
    /**
     * @var string
     */
    protected $repositoryLocation;

    /**
     * @var string
     */
    protected $localLocation;

    /**
     * BuildDetectionDatabaseService constructor.
     *
     * @param string $deepDetectEndpoint
     * @param string $repositoryLocation
     * @param string $localLocation
     */
    public function __construct(string $deepDetectEndpoint, string $repositoryLocation, string $localLocation)
    {
        parent::__construct($deepDetectEndpoint);
        $this->repositoryLocation = $repositoryLocation;
        $this->localLocation = $localLocation;
    }

    /**
     * @return string
     */
    public function getRepositoryLocation()
    {
        return $this->repositoryLocation;
    }

    /**
     * @param string $repositoryLocation
     */
    public function setRepositoryLocation(string $repositoryLocation)
    {
        $this->repositoryLocation = $repositoryLocation;
    }

    /**
     * @return string
     */
    public function getLocalLocation()
    {
        return $this->localLocation;
    }

    /**
     * @param string $localLocation
     */
    public function setLocalLocation(string $localLocation)
    {
        $this->localLocation = $localLocation;
    }

    /**
     * @param string $name
     * @param string $description
     * @param array $config
     * @throws Exception
     */
    public function createNewTextDatabase(string $name, string $description = "", $config = [])
    {
        $this->checkDatabaseName($name);
        $existsResponse = Request::get($this->deepDetectEndpoint . '/services/' . $name);
        if ($existsResponse->code != 404) {
            $this->deleteDatabase($name);
        }
        $config = array_replace_recursive($this->getBaseCreateConfig($name, $description), $config);
        if (isset($config['parameters']['mllib']['layers'])) {
            $config['parameters']['mllib']['layers'] = array_filter(array_values($config['parameters']['mllib']['layers']));
        }
        $this->createNewTextDatabaseFromConfig($name, $config);
    }

    /**
     * @param string $name
     */
    public function deleteDatabase(string $name): void
    {
        $this->checkDatabaseName($name);
        $response = Request::delete($this->deepDetectEndpoint . '/services/' . $name . '?clear=full', []);
        if (
            !isset($response->body->status) ||
            !isset($response->body->status->code) ||
            $response->body->status->code != 500
        ) {
            return;
        }
        Request::delete($this->deepDetectEndpoint . '/services/' . $name, []);
        if (file_exists($this->localLocation . '/' . $name . '/model.json')) {
            unlink($this->localLocation . '/' . $name . '/model.json');
        }
        if (file_exists($this->localLocation . '/' . $name . '/vocab.dat')) {
            unlink($this->localLocation . '/' . $name . '/vocab.dat');
        }
        if (!empty(glob($this->localLocation . '/' . $name . '/*.caffemodel'))) {
            array_map('unlink', glob($this->localLocation . '/' . $name . '/*.caffemodel'));
        }
        if (!empty(glob($this->localLocation . '/' . $name . '/*.prototxt'))) {
            array_map('unlink', glob($this->localLocation . '/' . $name . '/*.prototxt'));
        }
        if (!empty(glob($this->localLocation . '/' . $name . '/*.solverstate'))) {
            array_map('unlink', glob($this->localLocation . '/' . $name . '/*.solverstate'));
        }
    }

    /**
     * @param string $name
     * @param array $config
     */
    public function createNewTextDatabaseFromConfig(string $name, $config): void
    {
        $this->checkDatabaseName($name);
        Request::put($this->deepDetectEndpoint . '/services/' . $name, [], json_encode($config));
    }

    /**
     * @param string $name
     * @param array $config
     * @throws Exception
     */
    public function updateExistingTextDatabase(string $name, $config = [])
    {
        $this->checkDatabaseName($name);
        $config = array_replace_recursive($this->getBaseUpdateConfig($name), $config);
        $this->updateExistingTextDatabaseFromConfig($name, $config);
    }

    /**
     * @param string $name
     * @param array $config
     */
    public function updateExistingTextDatabaseFromConfig(string $name, $config): void
    {
        $this->checkDatabaseName($name);
        Request::put($this->deepDetectEndpoint . '/services/' . $name, [], json_encode($config));
    }

    /**
     * @param string $name
     * @param array $config
     * @return null|float
     */
    public function trainTextDatabaseFromConfig(string $name, $config = [])
    {
        $this->checkDatabaseName($name);
        $config = array_replace_recursive($this->getBaseTrainConfig($name), $config);
        $response = Request::post($this->deepDetectEndpoint . '/train', [], json_encode($config));
        if (
            !isset($response->body->body) ||
            !isset($response->body->body->measure) ||
            (
                !isset($response->body->body->measure->acc) &&
                !isset($response->body->body->measure->accp)
            )
        ) {
            return null;
        }
        return (float)($response->body->body->measure->acc??$response->body->body->measure->accp);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function inTrainingState(string $name)
    {
        $this->checkDatabaseName($name);
        $response = Request::get($this->deepDetectEndpoint . '/train?service=' . $name . '&job=1', []);
        if (
            !isset($response->body->head) ||
            !isset($response->body->head->status) ||
            $response->body->head->status != 'running'
        ) {
            return false;
        }
        return true;
    }

    /**
     * @param string $name
     * @param string $description
     * @return array
     */
    public function getBaseCreateConfig(string $name, string $description = ""): array
    {
        return [
            'mllib'       => 'caffe',
            'description' => $description,
            'type'        => 'supervised',
            'parameters'  => [
                'input' => ['connector' => 'txt',],
                'mllib' => [
                    'template'   => 'mlp',
                    'nclasses'   => count(glob($this->localLocation . '/' . $name . '/data/*/')),
                    'layers'     => [ // assoc keys are necessary for overriding existing config
                        '1st' => 200,
                        '2nd' => 200,
                    ],
                    'activation' => 'relu',
                ],
            ],
            'model'       => [
                'templates'  => '../templates/caffe/',
                'repository' => $this->repositoryLocation . '/' . $name,
            ],
        ];
    }

    /**
     * @param string $name
     * @return array
     */
    public function getBaseTrainConfig(string $name): array
    {
        return [
            'service'    => $name,
            'async'      => false,
            'parameters' => [
                'mllib'  => [
                    'gpu'    => true,
                    'solver' => [
                        'iterations'    => 10000,
                        'test_interval' => 200,
                        'base_lr'       => 0.05,
                    ],
                    'net'    => ['batch_size' => 400,],
                ],
                'input'  => [
                    'shuffle'         => true,
                    'test_split'      => 0.2,
                    'min_count'       => 10,
                    'min_word_length' => 5,
                    'count'           => true,
                ],
                'output' => [
                    'measure' => ['mcll', 'f1',],
                ],
            ],
            'data'       => [
                $this->repositoryLocation . '/' . $name . '/data',
            ],
        ];
    }

    /**
     * @return array
     */
    public function getBaseUpdateConfig($name): array
    {
        return [
            'mllib'       => 'caffe',
            'description' => 'newsgroup classification service',
            'type'        => 'supervised',
            'parameters'  => [
                'input' => [
                    'connector' => 'txt',
                ],
                'mllib' => ['nclasses' => 20,],
            ],
            'model'       => [
                'repository' => $this->repositoryLocation . '/' . $name,
            ],
        ];
    }
}
