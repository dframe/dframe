<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\Console;

use Dframe\Console\Exceptions\ConsoleException;
use Dframe\Loader\Loader;

/**
 * Class Command
 *
 * @package Dframe\Cli
 */
class Command extends Loader
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @param $args
     *
     * @return mixed
     * @throws ConsoleException
     */
    public function run($args)
    {
        $this->input = new ArrayArgs($args);
        $class = '\\Command\\' . $this->input->getName() . 'Command';
        $this->output = new OutputStyler();

        return call_user_func_array([new $class(), 'execute'], [$this->input, $this->output]);
    }
}
