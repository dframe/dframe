<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\Asset;

use Dframe\BaseException;
use Dframe\Router;
use Assetic\Asset\FileAsset;
use Assetic\Filter\CssImportFilter;
use Assetic\Filter\CssRewriteFilter;
use Assetic\Filter\PhpCssEmbedFilter;
use Assetic\Filter\CssMinFilter;
use Assetic\Filter;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetReference;
use Assetic\Filter\GoogleClosure;
use Patchwork\JSqueeze;

set_time_limit(120);

/**
 * Short Description
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class Assetic extends Router
{

    private function _checkDir($path)
    {
        if (!is_dir($path)) {
            if (!mkdir($path, 0755, true)) {
                throw new BaseException('Unable to create' . $path);
            }
        }
    }

    public function assetJs($sUrl = null, $path = null, $compress = true)
    {

        //Podstawowe sciezki
        $srcPath = $this->aRouting['assets']['assetsPath'] . $this->aRouting['assets']['assetsDir'] . '/' . $sUrl;
        if (is_null($path)) {
            $path = 'assets';
            if (isset($this->aRouting['assets']['assetsDir']) and !empty($this->aRouting['assets']['assetsDir'])) {
                $path = $this->aRouting['assets']['assetsDir'];
                $this->_checkDir($path);
            }
            $dstPath = $this->aRouting['assets']['cachePath'] . $path . '/' . $sUrl;
        } else {
            $dstPath = $this->aRouting['assets']['cachePath'] . $path;
        }


        //Kopiowanie pliku jezeli nie istnieje
        if (!file_exists($dstPath)) {
            if (!file_exists($srcPath)) {
                return $srcPath;
            }

            $pathinfo = pathinfo($dstPath);
            if (!file_exists($pathinfo['dirname'])) {
                mkdir($pathinfo['dirname'], 0777, true);
            }

            $js = file_get_contents($srcPath);
            if ($compress === true and $this->aRouting['assets']['minifyJsEnabled'] == true) {
                $jSqueeze = new JSqueeze();
                $js = $jSqueeze->squeeze($js, true, true, false);
            }


            if (!file_put_contents($dstPath, $js)) {
                $msg = date('Y-m-d h:m:s') . ' :: Unable to copy an asset From: ' . $srcPath . ' TO ' . $dstPath . "\n";
                $out = fopen(APP_DIR . '/View/logs/router.txt', "w");
                fwrite($out, $str);
                fclose($out);
            }
        }

        //Zwrocenie linku do kopii
        $sExpressionUrl = $sUrl;
        $sUrl = $this->requestPrefix . $this->aRouting['assets']['cacheUrl'] . $path . '/';
        $sUrl .= $sExpressionUrl;

        return $sUrl;
    }

    public function assetCss($sUrl = null, $path = null, $compress = true)
    {

        //Podstawowe sciezki
        $srcPath = $this->aRouting['assets']['assetsPath'] . $this->aRouting['assets']['assetsDir'] . '/' . $sUrl;
        if (is_null($path)) {
            $path = 'assets';
            if (isset($this->aRouting['assets']['assetsDir']) and !empty($this->aRouting['assets']['assetsDir'])) {
                $path = $this->aRouting['assets']['assetsDir'];
                $this->_checkDir($path);
            }
            $dstPath = $this->aRouting['assets']['cachePath'] . $path . '/' . $sUrl;
        } else {
            $dstPath = $this->aRouting['assets']['cachePath'] . $path;
        }


        //Kopiowanie pliku jezeli nie istnieje
        if (!file_exists($dstPath)) {
            if (!file_exists($srcPath)) {
                return '';
            }

            $pathinfo = pathinfo($dstPath);
            if (!file_exists($pathinfo['dirname'])) {
                mkdir($pathinfo['dirname'], 0755, true);
            }

            $args = array();
            //$args[] = new Yui\CssCompressorFilter('C:\yuicompressor-2.4.7\build\yuicompressor-2.4.7.jar', 'java'),

            if ($compress == true) {
                if ($this->aRouting['assets']['minifyCssEnabled'] == true) {
                    $args[] = new CssMinFilter();
                }

                $args[] = new PhpCssEmbedFilter();
                $args[] = new CssRewriteFilter();
                $args[] = new CssImportFilter();
            }

            $css = new AssetCollection(
                array(
                    new FileAsset($srcPath),
                ),
                $args
            );

            preg_match_all('/url\("([^\)]+?\.(woff2|woff|eot|ttf|svg|png|jpg|jpeg|gif))/', $css->dump(), $m);

            $srcPathinfo = pathinfo($srcPath);

            foreach ($m['1'] as $key => $url) {
                $subPathinfo = pathinfo($pathinfo['dirname'] . '/' . $url);
                if (!file_exists($subPathinfo['dirname'])) {
                    mkdir($subPathinfo['dirname'], 0777, true);
                }

                if (!copy($srcPathinfo['dirname'] . '/' . $url, $pathinfo['dirname'] . '/' . $url)) {
                    $msg = date('Y-m-d h:m:s') . ' :: Unable to copy an asset From: ' . $srcPathinfo['dirname'] . '/' . $url . ' TO ' . $pathinfo['dirname'] . '/' . $url . "\n";
                    $out = fopen(APP_DIR . 'View/logs/router.txt', "w");
                    fwrite($out, $str);
                    fclose($out);
                }
            }

            if (!file_put_contents($dstPath, $css->dump())) {
                $msg = date('Y-m-d h:m:s') . ' :: Unable to copy an asset From: ' . $srcPath . ' TO ' . $dstPath . "\n";
                $out = fopen(APP_DIR . '/View/logs/router.txt', "w");
                fwrite($out, $str);
                fclose($out);
            }
        }

        //Zwrocenie linku do kopii
        $sExpressionUrl = $sUrl;
        $sUrl = $this->requestPrefix . $this->aRouting['assets']['cacheUrl'] . $path . '/';
        $sUrl .= $sExpressionUrl;

        return $sUrl;
    }
}
