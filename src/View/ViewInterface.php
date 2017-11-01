<?php
/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\View;

/**
 * Short Description
 *
 * @author Sławek Kaleta <slaszka@gmail.com>
 */

interface ViewInterface
{

    /**
     * Set the var to the template
     *
     * @param string $name 
     * @param string $value
     */
    public function assign($name, $value);

    /**
     * Return code
     *
     * @param string $name - Filename
     * @param string $path - Alternative Path
     */
    public function fetch($name, $path = null);

    /**
     * Include File
     *
     * @param string $path 
     */
    public function renderInclude($path);
     
    /**
     * Display JSON.
     *
     * @param array $data
     * @param int   $status
     */
    public function renderJSON($data, $status);
 
    /**
     * Display JSONP.
     *
     * @param array $data Dane do wyświetlenia
     */
    public function renderJSONP($data);

}