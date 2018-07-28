<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\View;

use Dframe\Config;
use Dframe\View\Exceptions\ViewException;

/**
 * Smarty View
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class SmartyView implements \Dframe\View\ViewInterface
{
    public function __construct()
    {
        $this->smartyConfig = Config::load('view/smarty');

        $smarty = new \Smarty;
        $smarty->debugging = $this->smartyConfig->get('debugging', false);
        $smarty->setTemplateDir($this->smartyConfig->get('setTemplateDir'))
            ->setCompileDir($this->smartyConfig->get('setCompileDir'))
            ->addPluginsDir($this->smartyConfig->get('addPluginsDir'));

        $this->smarty = $smarty;
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
        try {
            if ($this->smarty->getTemplateVars($name) !== null) {
                throw new ViewException('You can\'t assign "' . $name . '" in Smarty');
            }

            $assign = $this->smarty->assign($name, $value);
        } catch (ViewException $e) {
            echo $e->getMessage() . '<br />
                File: ' . $e->getFile() . '<br />
                Code line: ' . $e->getLine() . '<br />
                Trace: ' . $e->getTraceAsString();
            exit();
        }

        return $assign;
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

        if ($path === null) {
            $path = $this->smarty->getTemplateDir(0) . DIRECTORY_SEPARATOR . $folder . $name . $this->smartyConfig->get('fileExtension', '.html.php');
        }

        try {
            if (!is_file($path)) {
                throw new ViewException('Can not open template ' . $name . ' in: ' . $path);
            }

            $fetch = $this->smarty->fetch($path); // Ładowanie widoku
        } catch (Exception $e) {
            echo $e->getMessage() . '<br />
                File: ' . $e->getFile() . '<br />
                Code line: ' . $e->getLine() . '<br />
                Trace: ' . $e->getTraceAsString();
            exit();
        }

        return $fetch;
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

        if ($path === null) {
            $path = $this->smarty->getTemplateDir(0) . DIRECTORY_SEPARATOR . $folder . $name . $this->smartyConfig->get('fileExtension', '.html.php');
        }

        try {
            if (!is_file($path)) {
                throw new ViewException('Can not open template ' . $name . ' in: ' . $path);
            }

            return $this->smarty->fetch($path); // Ładowanie widoku
        } catch (ViewException $e) {
            echo $e->getMessage() . '<br />
                File: ' . $e->getFile() . '<br />
                Code line: ' . $e->getLine() . '<br />
                Trace: ' . $e->getTraceAsString();
            exit();
        }
    }
}
