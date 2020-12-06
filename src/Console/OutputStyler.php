<?php

namespace Dframe\Console;

class OutputStyler implements OutputInterface
{
    /**
     * @param $messages
     */
    public function writeln($messages)
    {
        echo $messages;
    }
}