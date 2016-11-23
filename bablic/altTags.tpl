<!-- start Bablic Head {$version} -->
{foreach from=$locales item=locale}
    <link rel="alternate" href="{$locale[0]}" hreflang="{$locale[1]}">
{/foreach}
<script src="{$snippet_url}" {if $async eq true} async {/if}></script>
<!-- end Bablic Head -->