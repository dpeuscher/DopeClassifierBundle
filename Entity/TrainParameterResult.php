<?php

namespace Dope\ClassifierBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TrainParameterResult
 * @ORM\Table(name="train_parameter_result")
 * @ORM\Entity(repositoryClass="Dope\ClassifierBundle\Repository\TrainParameterResultRepository")
 */
class TrainParameterResult
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="dbName", type="string", length=255)
     */
    private $dbName;

    /**
     * @var int
     * @ORM\Column(name="version", type="integer")
     */
    private $version;

    /**
     * @var string
     * @ORM\Column(name="customConfig", type="string", length=2048, nullable=true)
     */
    private $customConfig;

    /**
     * @var int
     * @ORM\Column(name="iterations", type="integer")
     */
    private $iterations;

    /**
     * @var int
     * @ORM\Column(name="testInterval", type="integer")
     */
    private $testInterval;

    /**
     * @var float
     * @ORM\Column(name="baseLr", type="float")
     */
    private $baseLr;

    /**
     * @var int
     * @ORM\Column(name="batchSize", type="integer")
     */
    private $batchSize;

    /**
     * @var string
     * @ORM\Column(name="layers", type="string")
     */
    private $layers;

    /**
     * @var float
     * @ORM\Column(name="dedeAccuracy", type="float", nullable=true)
     */
    private $dedeAccuracy;

    /**
     * @var float
     * @ORM\Column(name="ownAccuracy", type="float", nullable=true)
     */
    private $ownAccuracy;

    /**
     * @var float
     * @ORM\Column(name="runtime", type="float", nullable=true)
     */
    private $runtime;

    /**
     * @var TrainParameterClassResult[]
     * @ORM\OneToMany(targetEntity="TrainParameterClassResult", mappedBy="trainParameterResult", cascade={"persist", "remove", "detach", "refresh"})
     */
    private $classResults;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get dbName
     *
     * @return string
     */
    public function getDbName()
    {
        return $this->dbName;
    }

    /**
     * Set dbName
     *
     * @param string $dbName
     * @return TrainParameterResult
     */
    public function setDbName($dbName)
    {
        $this->dbName = $dbName;

        return $this;
    }

    /**
     * Get iterations
     *
     * @return int
     */
    public function getIterations()
    {
        return $this->iterations;
    }

    /**
     * Set iterations
     *
     * @param integer $iterations
     * @return TrainParameterResult
     */
    public function setIterations($iterations)
    {
        $this->iterations = $iterations;

        return $this;
    }

    /**
     * Get testInterval
     *
     * @return int
     */
    public function getTestInterval()
    {
        return $this->testInterval;
    }

    /**
     * Set testInterval
     *
     * @param integer $testInterval
     * @return TrainParameterResult
     */
    public function setTestInterval($testInterval)
    {
        $this->testInterval = $testInterval;

        return $this;
    }

    /**
     * Get baseLr
     *
     * @return float
     */
    public function getBaseLr()
    {
        return $this->baseLr;
    }

    /**
     * Set baseLr
     *
     * @param float $baseLr
     * @return TrainParameterResult
     */
    public function setBaseLr($baseLr)
    {
        $this->baseLr = $baseLr;

        return $this;
    }

    /**
     * Get batchSize
     *
     * @return int
     */
    public function getBatchSize()
    {
        return $this->batchSize;
    }

    /**
     * Set batchSize
     *
     * @param integer $batchSize
     * @return TrainParameterResult
     */
    public function setBatchSize($batchSize)
    {
        $this->batchSize = $batchSize;

        return $this;
    }

    /**
     * Get dedeAccuracy
     *
     * @return float
     */
    public function getDedeAccuracy()
    {
        return $this->dedeAccuracy;
    }

    /**
     * Set dedeAccuracy
     *
     * @param float $dedeAccuracy
     * @return TrainParameterResult
     */
    public function setDedeAccuracy($dedeAccuracy)
    {
        $this->dedeAccuracy = $dedeAccuracy;

        return $this;
    }

    /**
     * Get ownAccuracy
     *
     * @return float
     */
    public function getOwnAccuracy()
    {
        return $this->ownAccuracy;
    }

    /**
     * Set ownAccuracy
     *
     * @param float $ownAccuracy
     * @return TrainParameterResult
     */
    public function setOwnAccuracy($ownAccuracy)
    {
        $this->ownAccuracy = $ownAccuracy;

        return $this;
    }

    /**
     * Get runtime
     *
     * @return float
     */
    public function getRuntime()
    {
        return $this->runtime;
    }

    /**
     * Set runtime
     *
     * @param float $runtime
     * @return TrainParameterResult
     */
    public function setRuntime($runtime)
    {
        $this->runtime = $runtime;

        return $this;
    }

    /**
     * Get version
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set version
     *
     * @param integer $version
     * @return TrainParameterResult
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get customConfig
     *
     * @return string
     */
    public function getCustomConfig()
    {
        return $this->customConfig;
    }

    /**
     * Set customConfig
     *
     * @param string $customConfig
     * @return TrainParameterResult
     */
    public function setCustomConfig($customConfig)
    {
        $this->customConfig = $customConfig;

        return $this;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->classResults = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set layers
     *
     * @param string $layers
     *
     * @return TrainParameterResult
     */
    public function setLayers(string $layers)
    {
        $this->layers = $layers;

        return $this;
    }

    /**
     * Get layers
     *
     * @return string
     */
    public function getLayers()
    {
        return $this->layers;
    }

    /**
     * Add classResult
     *
     * @param \Dope\ClassifierBundle\Entity\TrainParameterClassResult $classResult
     *
     * @return TrainParameterResult
     */
    public function addClassResult(\Dope\ClassifierBundle\Entity\TrainParameterClassResult $classResult)
    {
        $this->classResults[] = $classResult;

        return $this;
    }

    /**
     * Remove classResult
     *
     * @param \Dope\ClassifierBundle\Entity\TrainParameterClassResult $classResult
     */
    public function removeClassResult(\Dope\ClassifierBundle\Entity\TrainParameterClassResult $classResult)
    {
        $this->classResults->removeElement($classResult);
    }

    /**
     * Get classResults
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getClassResults()
    {
        return $this->classResults;
    }
}
