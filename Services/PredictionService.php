<?php

namespace Dope\ClassifierBundle\Services;

use Dope\ClassifierBundle\Services\Responses\PredictionResponse;
use Exception;
use Unirest\Request;

/**
 * @category  classifier
 * @copyright Copyright (c) 2017 CHECK24 Vergleichsportal Flüge GmbH
 */
class PredictionService extends AbstractDeepDetectService
{
    /**
     * @param string $name
     * @param string $text
     * @param array $config
     * @return \Dope\ClassifierBundle\Services\Responses\PredictionResponse
     * @throws Exception
     */
    public function predictText(string $name, string $text = '', $config = [])
    {
        $this->checkDatabaseName($name);
        $text = preg_replace([
            '/[^a-z0-9\.\-,:_;ßüöäÜÖÄ]/i',
            '/\xC3[^\x84\x96\x9C\xA4\xB6\xBC\x9F]/',
            '/[^\xC3]\x84/',
            '/[^\xC3]\x96/',
            '/[^\xC3]\x9C/',
            '/[^\xC3]\xA4/',
            '/[^\xC3]\xB6/',
            '/[^\xC3]\xBC/',
            '/[^\xC3]\x9F/',
            '/  +/',
        ], [
            ' ',
            ' ',
            ' ',
            ' ',
            ' ',
            ' ',
            ' ',
            ' ',
            ' ',
            ' ',
        ], $text);
        $config = array_replace_recursive($this->getBasePredictConfig($name, $text), $config);
        if (json_encode($config) === false) {
            throw new Exception("Could not transform config to json: " . var_export($config, true));
        }
        $response = Request::post($this->deepDetectEndpoint . '/predict', [], json_encode($config));
        return PredictionResponse::buildResponse($response->body);
    }

    /**
     * @param string $name
     * @param string $text
     * @return array
     */
    public function getBasePredictConfig(string $name, string $text = '')
    {
        return [
            'service'    => $name,
            'parameters' => [
                'mllib'  => ['gpu' => true,],
                'output' => [
                    'best' => 5,
                ],
            ],
            'data'       => [$text],
        ];
    }
}
