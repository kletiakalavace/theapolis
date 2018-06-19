<?php

namespace Theaterjobs\MainBundle\Utility;

/**
 * Interface CommandSchedulerInterface
 * @package Theaterjobs\MainBundle\Utility
 */
interface CommandSchedulerInterface
{
    /**
     * @return array
     */
    public function getIds();

    /**
     * @return string
     */
    public function getJobName();

    /**
     * @return integer
     */
    public function getChunkSize();
}
