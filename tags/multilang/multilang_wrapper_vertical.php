<html>
<wb-var lid="lng{{wbNewId()}}" />
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-2 pb-2 d-block">
            <div class="nav nav-pills d-sm-block" id="{{_var.lid}}" role="tablist" aria-orientation="vertical">
                <wb-foreach wb-from='lang'>
                    <wb-var active=" active " wb-if='"{{_idx}}" == "0"' else="" />
                    <a class="nav-link ml-0 {{_var.active}} d-inline d-sm-block nobr" id="{{_var.lid}}-{{_key}}-tab"
                        data-toggle="pill" href="#{{_var.lid}}-{{_key}}" role="tab" aria-controls="v-pills-home"
                        aria-selected="true">
                        {{_origkey._origkey}}&nbsp;&nbsp;
                        <img src='/engine/lib/fonts/flag-icon-css/flags/4x3/{{_key}}.svg' class="mod-multilang-flag border" width='16'
                            height="12">
                    </a>
                </wb-foreach>
            </div>
        </div>
        <div class="col-sm-10">
            <div class="tab-content">
                <wb-foreach wb-from='lang'>
                    <div class="tab-pane fade wb-multilang-row" id="{{_var.lid}}-{{_key}}" data-id="{{_key}}"
                        data-lang="{{_key}}" role="tabpanel" aria-labelledby="{{_var.lid}}-{{_key}}-tab">
                    </div>
                </wb-foreach>
            </div>
        </div>
    </div>
</div>
<textarea type='json' class='wb-multilang-data' style='display:none;'></textarea>

</html>