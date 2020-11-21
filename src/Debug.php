<?php

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta.
 *
 * @license https://github.com/dframe/dframe/blob/master/LICENCE (MIT)
 */

namespace Dframe;

/**
 * Debug Class.
 *
 * @author Sławomir Kaleta <slaszka@gmail.com>
 */
class Debug
{
    /**
     * @var array
     */
    protected array $headers = [];

    /**
     * @param $headers
     *
     * @return $this
     */
    public function addHeader($headers): self
    {
        $this->headers = array_unique(array_merge($this->headers, $headers));

        return $this;
    }

    /**
     * @return array
     */
    public function getHeader(): array
    {
        return $this->headers;
    }
}
