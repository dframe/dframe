<?php
namespace Dframe\View;

interface interfaceView
{

    /**
     * Set the var to the template
     *
     * @param string $name 
     * @param string $value
     *
     * @return void
     */

    public function assign($name, $value);

    /**
     * Return code
     *
     * @param string $name - Filename
     * @param string $path - Alternative Path
     *
     * @return void
     */
    public function fetch($name, $path=null);

    /**
     * Include File
     */
    public function renderInclude($path);
     
    /**
     * Display JSON.
     * @param array $data
     */
    public function renderJSON($data);
 
    /**
     * Display JSONP.
     * @param array $data Dane do wyświetlenia
     */
    public function renderJSONP($data);

}