<?php

namespace Dope\ClassifierBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TrainParameterClassResult
 *
 * @ORM\Table(name="train_parameter_class_result")
 * @ORM\Entity(repositoryClass="Dope\ClassifierBundle\Repository\TrainParameterClassResultRepository")
 */
class TrainParameterClassResult
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="className", type="string", length=255)
     */
    private $className;

    /**
     * @var int
     *
     * @ORM\Column(name="count", type="integer")
     */
    private $count;

    /**
     * @var float
     *
     * @ORM\Column(name="accuracy", type="float")
     */
    private $accuracy;

    /**
     * @var TrainParameterResult
     * @ORM\ManyToOne(targetEntity="TrainParameterResult", inversedBy="classResults")
     * @ORM\JoinColumn(name="train_parameter_result_id", referencedColumnName="id")
     */
    private $trainParameterResult;


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
     * Set count
     *
     * @param integer $count
     *
     * @return TrainParameterClassResult
     */
    public function setCount($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * Get count
     *
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Set accuracy
     *
     * @param float $accuracy
     *
     * @return TrainParameterClassResult
     */
    public function setAccuracy($accuracy)
    {
        $this->accuracy = $accuracy;

        return $this;
    }

    /**
     * Get accuracy
     *
     * @return float
     */
    public function getAccuracy()
    {
        return $this->accuracy;
    }

    /**
     * Set trainParameterResult
     *
     * @param \Dope\ClassifierBundle\Entity\TrainParameterResult $trainParameterResult
     *
     * @return TrainParameterClassResult
     */
    public function setTrainParameterResult(\Dope\ClassifierBundle\Entity\TrainParameterResult $trainParameterResult = null)
    {
        $this->trainParameterResult = $trainParameterResult;

        return $this;
    }

    /**
     * Get trainParameterResult
     *
     * @return \Dope\ClassifierBundle\Entity\TrainParameterResult
     */
    public function getTrainParameterResult()
    {
        return $this->trainParameterResult;
    }

    /**
     * Set className
     *
     * @param string $className
     *
     * @return TrainParameterClassResult
     */
    public function setClassName($className)
    {
        $this->className = $className;

        return $this;
    }

    /**
     * Get className
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }
}
