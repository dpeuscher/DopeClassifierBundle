<?php

namespace Dope\ClassifierBundle\Services;

use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * @category  classifier
 * @copyright Copyright (c) 2017 CHECK24 Vergleichsportal Flüge GmbH
 */
class ClassFileWriter implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var string
     */
    protected $writeFolder;

    /**
     * ClassFileWriter constructor.
     *
     * @param string $writeFolder
     * @param LoggerInterface $logger
     */
    public function __construct(string $writeFolder, LoggerInterface $logger)
    {
        $this->writeFolder = $writeFolder;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public function getWriteFolder()
    {
        return $this->writeFolder;
    }

    /**
     * @param string $writeFolder
     */
    public function setWriteFolder(string $writeFolder)
    {
        $this->writeFolder = $writeFolder;
    }

    /**
     * @param string $database
     * @param string $class
     * @param string $text
     * @return bool
     * @throws Exception
     */
    public function writeClassForTextClassifier(string $database, string $class, string $text)
    {
        $class = preg_replace('/[&]/', '_', $class);

        if (!is_dir($this->writeFolder . '/' . $database)) {
            if (!mkdir($this->writeFolder . '/' . $database)) {
                throw new Exception("Could not create folder: " . $this->writeFolder . '/' . $database . '/' . $class);
            }
        }
        if (!is_dir($this->writeFolder . '/' . $database . '/data')) {
            if (!mkdir($this->writeFolder . '/' . $database . '/data')) {
                throw new Exception("Could not create folder: " . $this->writeFolder . '/' . $database . '/data');
            }
        }
        if (!is_dir($this->writeFolder . '/' . $database . '/data/' . $class)) {
            if (!mkdir($this->writeFolder . '/' . $database . '/data/' . $class)) {
                throw new Exception("Could not create folder: " . $this->writeFolder . '/' . $database . '/data/' . $class);
            }
        }
        $fileName = $this->writeFolder . '/' . $database . '/data/' . $class . '/' . substr(md5($text), 0, 8) . '.txt';

        if (file_exists($fileName)) {
            $this->logger->warning($fileName . ' already exists - skipping');
            return false;
        }

        file_put_contents($fileName, $text);

        return true;
    }
    /**
     * @param string $database
     * @param string $class
     * @param string $text
     * @return bool
     * @throws Exception
     */
    public function writeClassForTextTest(string $database, string $class, string $text)
    {
        $class = preg_replace('/[&]/', '_', $class);

        if (!is_dir($this->writeFolder . '/' . $database)) {
            if (!mkdir($this->writeFolder . '/' . $database)) {
                throw new Exception("Could not create folder: " . $this->writeFolder . '/' . $database . '/' . $class);
            }
        }
        if (!is_dir($this->writeFolder . '/' . $database . '/test')) {
            if (!mkdir($this->writeFolder . '/' . $database . '/test')) {
                throw new Exception("Could not create folder: " . $this->writeFolder . '/' . $database . '/test');
            }
        }
        if (!is_dir($this->writeFolder . '/' . $database . '/test/' . $class)) {
            if (!mkdir($this->writeFolder . '/' . $database . '/test/' . $class)) {
                throw new Exception("Could not create folder: " . $this->writeFolder . '/' . $database . '/test/' . $class);
            }
        }
        $fileName = $this->writeFolder . '/' . $database . '/test/' . $class . '/' . substr(md5($text), 0, 8) . '.txt';

        if (file_exists($fileName)) {
            $this->logger->warning($fileName . ' already exists - skipping');
            return false;
        }

        file_put_contents($fileName, $text);

        return true;
    }

    /**
     * @param string $database
     * @return array
     */
    public function getTestData(string $database) {
        $testValues = [];
        foreach (glob($this->writeFolder . '/' . $database . '/test/*/*') as $path) {
            $testValues[] = ['path' => $path, 'class' => basename(dirname($path))];
        }
        return $testValues;
    }
}
