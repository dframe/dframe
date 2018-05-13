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
 * @author Sławomir Kaleta <slaszka@gmail.com>
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
}
