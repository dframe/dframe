<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\View;

use Dframe\Config;
use Dframe\View\Exception\ViewException;

/**
 * Default View
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 * @author Amadeusz Dzięcioł <amadeusz.xd@gmail.com>
 */
class DefaultView implements \Dframe\View\ViewInterface
{

    public function __construct()
    {
        $this->templateConfig = Config::load('view/default');
    }

    /**
     * Set the var to the template
     *
     * @param string $name
     * @param string $value
     *
     * @return void
     */
    public function assign($name, $value)
    {
        $this->$name = $value;
    }

    /**
     * Return code
     *
     * @param string $name Filename
     * @param string $path Alternative Path
     *
     * @return void
     */
    public function fetch($name, $path = null)
    {
        $pathFile = pathFile($name);
        $folder = $pathFile[0];
        $name = $pathFile[1];

        if ($path == null) {
            $path = $this->templateConfig->get('setTemplateDir') . DIRECTORY_SEPARATOR . $folder . $name . $this->templateConfig->get('fileExtension', '.html.php');
        }

        try {
            if (!is_file($path)) {
                throw new ViewException('Can not open template ' . $name . ' in: ' . $path);
            }
            ob_start();
            include $path;
        } catch (ViewException $e) {
            echo $e->getMessage() . '<br />
                File: ' . $e->getFile() . '<br />
                Code line: ' . $e->getLine() . '<br />
                Trace: ' . $e->getTraceAsString();
            exit();
        }

        return ob_get_clean();
    }

    /**
     * Przekazuje kod do szablonu Smarty
     *
     * @param string $name
     * @param string $path
     *
     * @return void
     */
    public function renderInclude($name, $path = null)
    {

        $pathFile = pathFile($name);
        $folder = $pathFile[0];
        $name = $pathFile[1];

        if ($path == null) {
            $path = $this->templateConfig->get('setTemplateDir') . DIRECTORY_SEPARATOR . $folder . $name . $this->templateConfig->get('fileExtension', '.html.php');
        }

        try {
            if (!is_file($path)) {
                throw new ViewException('Can not open template ' . $name . ' in: ' . $path);
            }

            $renderInclude = include $path;
        } catch (ViewException $e) {
            echo $e->getMessage() . '<br />
                File: ' . $e->getFile() . '<br />
                Code line: ' . $e->getLine() . '<br />
                Trace: ' . $e->getTraceAsString();
            exit();
        }

        return $renderInclude;
    }
}
