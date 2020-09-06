<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\Console;

/**
 * Class Command
 *
 * @package Dframe\Cli
 */
class Command extends \Dframe\Loader\Loader
{

    /**
     * @param $args
     *
     * @return mixed
     */
    public function run($args)
    {
        $start = $this->start($args);

        $class = '\Command\\' . $start['commands'][0] . 'Command';
        return call_user_func_array([new $class, $start['commands'][1]], $start['options']);
    }

    /**
     * @param $args
     *
     * @return array
     */
    public function start($args)
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
                    list($com, $value) = explode("=", $com, 2);
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

        return $ret;
    }
}
