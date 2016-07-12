<?php
namespace Dframe\View;

interface interfaceView
{

    /**
     * Przekazuje kod do szablonu
     *
     * @param string $name Nazwa pliku
     * @param string $path Ścieżka do szablonu
     *
     * @return void
     */

    public function assign($name, $value);

    public function fetch($name, $path=null);

    public function renderInclude($name);
     
    /**
     * Wyświetla dane JSON.
     * @param array $data Dane do wyświetlenia
     */
    public function renderJSON($data);
 
    /**
     * Wyświetla dane JSONP.
     * @param array $data Dane do wyświetlenia
     */
    public function renderJSONP($data);

}