<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe;

use Dframe\Router\Response;
use Dframe\View\Exceptions\ViewException;
use Dframe\View\ViewInterface;


/**
 * View Class.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
abstract class View extends Loader implements ViewInterface
{
    /**
     * Path Templates
     *
     * @var string
     */
    public $dir;

    /**
     * Defines template variables.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return mix
     */
    public function assign($name, $value)
    {
        if (!isset($this->view)) {
            throw new ViewException('Please Define view engine in app/View.php', 500);
        }

        return $this->view->assign($name, $value);
    }

    /**
     * Generates the output of the templates with parsing all the template variables.
     *
     * @param string $data
     * @param string $type
     *
     * @return mix|Json|String
     */
    public function render($data, $type = null)
    {
        if (empty($type) or $type === 'html') {
            return Response::Create($this->renderInclude($data));
        } elseif ($type === 'jsonp') {
            return $this->renderJSONP($data);
        } else {
            return $this->renderJSON($data);
        }
    }

    /**
     * Fetch the output of the templates with parsing all the template variables.
     *
     * @param string $name
     * @param string $path
     *
     * @return mixed
     */
    public function fetch($name, $path = null)
    {
        if (!isset($this->view)) {
            throw new ViewException('Please Define view engine in app/View.php', 500);
        }

        if (!is_null($this->dir)) {
            $this->view->setTemplateDir($this->dir);
        }

        return $this->view->fetch($name, $path);
    }

    /**
     * Include pliku.
     *
     * @param string $name
     * @param null $path
     *
     * @return mixed
     */
    public function renderInclude($name, $path = null)
    {
        if (!isset($this->view)) {
            throw new ViewException('Please Define view engine in app/View.php', 500);
        }

        if (!is_null($this->dir)) {
            $this->view->setTemplateDir($this->dir);
        }

        return $this->view->renderInclude($name, $path);
    }

    /**
     * Display JSON.
     *
     * @param array $data
     * @param int $status
     */
    public function renderJSON($data, $status = 200)
    {
        exit(Response::Create(json_encode($data))->status($status)->headers(['Content-Type' => 'application/json'])->display());
    }

    /**
     * Display JSONP.
     *
     * @param array $data
     */
    public function renderJSONP($data)
    {
        $callback = null;
        if (isset($_GET['callback'])) {
            $callback = $_GET['callback'];
        }

        exit(Response::Create($callback . '(' . json_encode($data) . ')')->headers(['Content-Type' => 'application/jsonp'])->display());
    }
}
