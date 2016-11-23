<!-- start Bablic Head {$version|escape:'htmlall':'UTF-8'} -->
{foreach from=$locales item=locale}
    <link rel="alternate" href="{$locale[0]|escape:'htmlall':'UTF-8'}" hreflang="{$locale[1]|escape:'htmlall':'UTF-8'}">
{/foreach}
<script src="{$snippet_url|escape:'htmlall':'UTF-8'}" {if $async eq true} async {/if}></script>
<!-- end Bablic Head -->