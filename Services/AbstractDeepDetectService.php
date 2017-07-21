<?php

namespace Dope\ClassifierBundle\Services;

use Exception;

/**
 * @category  classifier
 * @copyright Copyright (c) 2017 CHECK24 Vergleichsportal Flüge GmbH
 */
abstract class AbstractDeepDetectService
{
    /**
     * @var string
     */
    protected $deepDetectEndpoint;

    /**
     * AbstractDeepDetectService constructor.
     *
     * @param string $deepDetectEndpoint
     */
    public function __construct(string $deepDetectEndpoint)
    {
        $this->deepDetectEndpoint = $deepDetectEndpoint;
    }

    /**
     * @param string $name
     * @throws Exception
     */
    protected function checkDatabaseName(string $name): void
    {
        if (preg_match('/[^a-zA-Z0-9.\-_]/', $name)) {
            throw new Exception("Illegal name for a database (should be url-compatible)");
        }
    }
}
