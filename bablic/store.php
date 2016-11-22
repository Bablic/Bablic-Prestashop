<?php
/**
 * Bablic Localization.
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @category  localization
 *
 * @author    Ishai Jaffe <ishai@bablic.com>
 * @copyright Bablic 2016
 * @license   http://www.gnu.org/licenses/ GNU License
 */

class BablicPrestashopStore
{
    public function get($key)
    {
        return Configuration::get('bablic'.$key);
    }
    public function set($key, $value)
    {
        Configuration::updateValue('bablic'.$key, $value, true);
    }
}
