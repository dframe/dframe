<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe\Asset;

use Dframe\Router;
use Assetic\Asset\FileAsset;
use Assetic\Filter\CssImportFilter;
use Assetic\Filter\CssMinFilter;
use Assetic\Filter\CssRewriteFilter;
use Assetic\Filter\PhpCssEmbedFilter;
use Assetic\Asset\AssetCollection;
use Patchwork\JSqueeze;
use Dframe\Asset\Exceptions\AsseticException;

set_time_limit(120);

/**
 * Short Description.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class Assetic extends Router
{
    /**
     * @param $path
     *
     * @throws AsseticException
     */
    private function checkDir($path)
    {
        if (!is_dir($path)) {
            if (!mkdir($path, 0777, true)) {
                throw new AsseticException('Unable to create' . $path, '', 403);
            }
        }
    }

    /**
     * @param null $sUrl
     * @param null $path
     * @param bool $compress
     *
     * @return null|string
     * @throws AsseticException
     */
    public function assetJs($sUrl = null, $path = null, $compress = true)
    {

        // Basic paths
        $srcPath = $this->aRouting['assets']['assetsPath'] . $this->aRouting['assets']['assetsDir'] . '/' . $sUrl;
        if (is_null($path)) {
            $path = 'assets';
            if (isset($this->aRouting['assets']['assetsDir']) and !empty($this->aRouting['assets']['assetsDir'])) {
                $path = $this->aRouting['assets']['assetsDir'];
                $this->checkDir($path);
            }
            $dstPath = $this->aRouting['assets']['cachePath'] . $path . '/' . $sUrl;
        } else {
            $dstPath = $this->aRouting['assets']['cachePath'] . $path;
        }

        // Copying a file if it does not exist
        if (!file_exists($dstPath)) {
            if (!file_exists($srcPath)) {
                return $srcPath;
            }

            $pathInfo = pathinfo($dstPath);
            if (!file_exists($pathInfo['dirname'])) {
                if (!mkdir($pathInfo['dirname'], 0777, true)) {
                    throw new AsseticException('Unable to create' . $path, '', 403);
                }
            }

            $js = file_get_contents($srcPath);
            if ($compress === true and $this->aRouting['assets']['minifyJsEnabled'] == true) {
                $jSqueeze = new JSqueeze();
                $js = $jSqueeze->squeeze($js, true, true, false);
            }

            if (!file_put_contents($dstPath, $js)) {
                $msg = date('Y-m-d h:m:s') . ' :: Unable to copy an asset From: ' . $srcPath . ' TO ' . $dstPath . "\n";
                $out = fopen(APP_DIR . '/View/logs/router.txt', 'w');
                fwrite($out, $msg);
                fclose($out);
            }
        }

        // Return the link to the copy
        $sExpressionUrl = $sUrl;
        $sUrl = $this->requestPrefix . $this->aRouting['assets']['cacheUrl'] . $path . '/';
        $sUrl .= $sExpressionUrl;

        return $sUrl;
    }

    /**
     * @param null $sUrl
     * @param null $path
     * @param bool $compress
     *
     * @return null|string
     * @throws AsseticException
     */
    public function assetCss($sUrl = null, $path = null, $compress = true)
    {

        // Basic paths
        $srcPath = $this->aRouting['assets']['assetsPath'] . $this->aRouting['assets']['assetsDir'] . '/' . $sUrl;
        if (is_null($path)) {
            $path = 'assets';
            if (isset($this->aRouting['assets']['assetsDir']) and !empty($this->aRouting['assets']['assetsDir'])) {
                $path = $this->aRouting['assets']['assetsDir'];
                $this->checkDir($path);
            }
            $dstPath = $this->aRouting['assets']['cachePath'] . $path . '/' . $sUrl;
        } else {
            $dstPath = $this->aRouting['assets']['cachePath'] . $path;
        }

        // Copying a file if it does not exist
        if (!file_exists($dstPath)) {
            if (!file_exists($srcPath)) {
                return '';
            }

            $pathInfo = pathinfo($dstPath);
            if (!file_exists($pathInfo['dirname'])) {
                if (!mkdir($pathInfo['dirname'], 0777, true)) {
                    throw new AsseticException('Unable to create' . $path, '', 403);
                }
            }

            $args = [];
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
                [
                    new FileAsset($srcPath),
                ],
                $args
            );

            preg_match_all('/url\("([^\)]+?\.(woff2|woff|eot|ttf|svg|png|jpg|jpeg|gif))/', $css->dump(), $m);

            $srcPathInfo = pathinfo($srcPath);

            foreach ($m['1'] as $key => $url) {
                $subPathInfo = pathinfo($pathInfo['dirname'] . '/' . $url);
                if (!file_exists($subPathInfo['dirname'])) {
                    if (!mkdir($subPathInfo['dirname'], 0777, true)) {
                        throw new AsseticException('Unable to create' . $path, '', 403);
                    }
                }

                if (!copy($srcPathInfo['dirname'] . '/' . $url, $pathInfo['dirname'] . '/' . $url)) {
                    $msg = date('Y-m-d h:m:s') . ' :: Unable to copy an asset From: ' . $srcPathInfo['dirname'] . '/' . $url . ' TO ' . $pathInfo['dirname'] . '/' . $url . "\n";
                    $out = fopen(APP_DIR . 'View/logs/router.txt', 'w');
                    fwrite($out, $msg);
                    fclose($out);
                }
            }

            if (!file_put_contents($dstPath, $css->dump())) {
                $msg = date('Y-m-d h:m:s') . ' :: Unable to copy an asset From: ' . $srcPath . ' TO ' . $dstPath . "\n";
                $out = fopen(APP_DIR . '/View/logs/router.txt', 'w');
                fwrite($out, $msg);
                fclose($out);
            }
        }

        // Return the link to the copy
        $sExpressionUrl = $sUrl;
        $sUrl = $this->requestPrefix . $this->aRouting['assets']['cacheUrl'] . $path . '/';
        $sUrl .= $sExpressionUrl;

        return $sUrl;
    }
}
