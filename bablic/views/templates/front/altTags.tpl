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

<!-- start Bablic Head {$version} -->
{foreach from=$locales item=locale}
    <link rel="alternate" href="{$locale[0]}" hreflang="{$locale[1]}">
{/foreach}
<script src="{$snippet_url}" {if $async eq true} async {/if}></script>
<!-- end Bablic Head -->