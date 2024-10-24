{if $mktr_status}
{assign var="mktr_status" value=false scope="global"}
<script type="text/javascript">{$mktr nofilter}</script>
{$mktr_events nofilter}
{/if}
