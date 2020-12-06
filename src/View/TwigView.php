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
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

/**
 * Twig View.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class TwigView implements ViewInterface
{
    /**
     * @var \Twig\Environment
     */
    public $twig;

    /**
     * @var array
     */
    public $assign;

    /**
     * TwigView constructor.
     */
    public function __construct()
    {
        $twigConfig = Config::load('view/twig');
        $loader = new FilesystemLoader($twigConfig->get('setTemplateDir'));
        $twig = new Environment(
            $loader,
            [
                'cache' => $twigConfig->get('setCompileDir'),
            ]
        );
        $this->twig = $twig;
    }

    /**
     * Transfers the code to the twig template.
     *
     * @param string $name
     * @param string $path
     *
     * @return mixed
     * @throws ViewException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function assign($name, $value)
    {
        if (isset($this->assign[$name])) {
            throw new ViewException('You can\'t assign "' . $name . '" in Twig');
        }

        $assign = $this->assign[$name] = $value;
        return $assign;
    }

    /**
     * Return code.
     *
     * @param string $name
     * @param string $path
     *
     * @return mixed
     * @throws ViewException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
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
     * @throws ViewException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderInclude($name, $path = null)
    {
        $twigConfig = Config::load('twig');
        $pathFile = pathFile($name);
        $folder = $pathFile[0];
        $name = $pathFile[1];
        $path = $twigConfig->get('setTemplateDir') . DIRECTORY_SEPARATOR
            . $folder . $name . $twigConfig->get('fileExtension', '.twig');

        if (!is_file($path)) {
            throw new ViewException('Can not open template ' . $name . ' in: ' . $path);
        }

        $renderInclude = $this->twig->render($name, $this->assign);
        return $renderInclude;
    }
}
