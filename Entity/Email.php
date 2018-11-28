<?php
/**
 * Created by PhpStorm.
 * User: julius
 * Date: 02.07.18
 * Time: 12:30
 */

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Email
 * @package App\Entity
 * @ORM\Entity(repositoryClass="EmailRepository")
 */
class Email
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ResultSnapshot", inversedBy="emails")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private $resultSnapshot;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\AnalysisSnapshot", inversedBy="emails")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private $analysisSnapshot;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SharedResult", inversedBy="emails")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private $dynamicResult;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SharedAnalysis", inversedBy="emails")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private $dynamicAnalysis;

    /**
     * @Assert\Email
     * @ORM\Column(type="string", nullable=false)
     */
    private $emailAddress;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Email
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResultSnapshot()
    {
        return $this->resultSnapshot;
    }

    /**
     * @param mixed $resultSnapshot
     * @return Email
     */
    public function setResultSnapshot($resultSnapshot)
    {
        $this->resultSnapshot = $resultSnapshot;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAnalysisSnapshot()
    {
        return $this->analysisSnapshot;
    }

    /**
     * @param mixed $analysisSnapshot
     * @return Email
     */
    public function setAnalysisSnapshot($analysisSnapshot)
    {
        $this->analysisSnapshot = $analysisSnapshot;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDynamicResult()
    {
        return $this->dynamicResult;
    }

    /**
     * @param mixed $dynamicResult
     * @return Email
     */
    public function setDynamicResult($dynamicResult)
    {
        $this->dynamicResult = $dynamicResult;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDynamicAnalysis()
    {
        return $this->dynamicAnalysis;
    }

    /**
     * @param mixed $dynamicAnalysis
     * @return Email
     */
    public function setDynamicAnalysis($dynamicAnalysis)
    {
        $this->dynamicAnalysis = $dynamicAnalysis;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @param mixed $emailAddress
     * @return Email
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
        return $this;
    }

}
