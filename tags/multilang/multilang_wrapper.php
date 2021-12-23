<html>
<wb-var lid="lng{{wbNewId()}}" />
<ul class="nav nav-tabs" id="{{_var.lid}}" role="tablist">
    <wb-foreach wb="from=lang&tpl=false" >
    <li class="nav-item">
        <a class="nav-link nobr" id="{{_var.lid}}-{{_key}}-tab" data-toggle="tab" href="#{{_var.lid}}-{{_key}}" role="tab" aria-selected="true">
            {{_origkey._origkey}}&nbsp;&nbsp;<img src='/engine/lib/fonts/flag-icon-css/flags/4x3/{{_key}}.svg' class="mod-multilang-flag border" width='16' height="12">
        </a>
    </li>
    </wb-foreach>
</ul>

<div class="tab-content pt-4">
    <wb-foreach wb="from=lang&tpl=false">
        <div class="tab-pane fade wb-multilang-row" id="{{_var.lid}}-{{_key}}" data-id="{{_key}}" data-lang="{{_key}}" role="tabpanel" aria-labelledby="{{_var.lid}}-{{_key}}-tab">
        </div>
    </wb-foreach>
</div>
<textarea type='json' class='wb-multilang-data' style='display:none;'></textarea>
</html>
