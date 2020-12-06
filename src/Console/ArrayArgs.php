<?php

namespace Dframe\Console;

use Dframe\Console\Exceptions\ConsoleException;

class ArrayArgs implements InputInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param $args
     *
     * @return array
     * @throws \Dframe\Console\Exceptions\ConsoleException
     */
    public function __construct($args)
    {
        array_shift($args);
        $endOfOptions = false;

        $ret = [
            'commands' => [],
            'options' => [],
            'flags' => [],
            'arguments' => [],
        ];

        while ($arg = array_shift($args)) {
            // if we have reached end of options,
            // we cast all remaining argv's as arguments
            if ($endOfOptions) {
                $ret['arguments'][] = $arg;
                continue;
            }

            // Is it a command? (prefixed with --)
            if (substr($arg, 0, 2) === '--') {
                // is it the end of options flag?
                if (!isset($arg[3])) {
                    $endOfOptions = true; // end of options;
                    continue;
                }

                $value = "";
                $com = substr($arg, 2);

                // is it the syntax '--option=argument'?
                if (strpos($com, '=')) {
                    [$com, $value] = explode("=", $com, 2);
                } elseif (strpos(
                        $args[0],
                        '-'
                    ) !== 0) { // is the option not followed by another option but by arguments
                    while (strpos($args[0], '-') !== 0) {
                        $value .= array_shift($args) . ' ';
                    }
                    $value = rtrim($value, ' ');
                }

                $ret['options'][$com] = !empty($value) ? $value : true;
                continue;
            }

            // Is it a flag or a serial of flags? (prefixed with -)
            if (substr($arg, 0, 1) === '-') {
                for ($i = 1; isset($arg[$i]); $i++) {
                    $ret['flags'][] = $arg[$i];
                }
                continue;
            }

            // finally, it is not option, nor flag, nor argument
            $ret['commands'][] = $arg;
            continue;
        }

        if (!count($ret['options']) && !count($ret['flags'])) {
            $ret['arguments'] = array_merge($ret['commands'], $ret['arguments']);
            $ret['commands'] = [];
        }

        if (isset($ret['commands'][0])) {
            $name = $ret['commands'][0];
        } elseif (isset($ret['arguments'][0])) {
            $name = $ret['commands'][0];
        } else {
            throw new ConsoleException('Invalid File.');
        }

        $this->setName($name);
        $this->setOptions($ret['options']);
        return $ret;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}