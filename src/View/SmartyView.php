<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\View;

use Dframe\Config\Config;
use Dframe\View\Exceptions\ViewException;
use Smarty;
use SmartyException;

/**
 * Smarty View.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class SmartyView implements ViewInterface
{
    /**
     * @var Smarty
     */
    public $smarty;

    /**
     * @var Config
     */
    protected $smartyConfig;

    /**
     * SmartyView constructor.
     */
    public function __construct()
    {
        $this->smartyConfig = Config::load('view/smarty');

        $smarty = new Smarty();
        $smarty->debugging = $this->smartyConfig->get('debugging', false);
        $smarty->setTemplateDir($this->smartyConfig->get('setTemplateDir'))
            ->setCompileDir($this->smartyConfig->get('setCompileDir'))
            ->addPluginsDir($this->smartyConfig->get('addPluginsDir'));

        $this->smarty = $smarty;
    }

    /**
     * @param $dir
     */
    public function setTemplateDir($dir)
    {
        $this->smarty->setTemplateDir($dir);
    }

    /**
     * Set the var to the template.
     *
     * @param string $name
     * @param        $value
     *
     * @return mixed
     * @throws ViewException
     */
    public function assign($name, $value)
    {
        if ($this->smarty->getTemplateVars($name) !== null) {
            throw new ViewException('You can\'t assign "' . $name . '" in Smarty');
        }

        $assign = $this->smarty->assign($name, $value);


        return $assign;
    }

    /**
     * Return code.
     *
     * @param string $name
     * @param string $path
     *
     * @return mixed
     * @throws SmartyException
     * @throws ViewException
     */
    public function fetch($name, $path = null)
    {
        return $this->renderInclude($name, $path);
    }

    /**
     * Transfers the code to the Smarty template.
     *
     * @param string $name
     * @param string $path
     *
     * @return mixed
     * @throws SmartyException
     * @throws ViewException
     */
    public function renderInclude($name, $path = null)
    {
        $pathFile = pathFile($name);
        $folder = $pathFile[0];
        $name = $pathFile[1];

        if ($path === null) {
            $path = $this->smarty->getTemplateDir(0) . DIRECTORY_SEPARATOR . $folder . $name .
                $this->smartyConfig->get('fileExtension', '.html.php');
        }

        if (!is_file($path)) {
            throw new ViewException('Can not open template ' . $name . ' in: ' . $path);
        }

        return $this->smarty->fetch($path); // Loading view
    }
}
