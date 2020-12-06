<?php

namespace Dframe\Console;

interface OutputInterface
{
    /**
     * @param $messages
     */
    public function writeln($messages);
}