<?php

namespace Dframe\Console;

interface InputInterface
{
    /**
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param array $options
     *
     * @return void
     */
    public function setOptions(array $options);

    /**
     * @return array
     */
    public function getOptions();
}