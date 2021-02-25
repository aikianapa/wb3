<html>
<wb-var lid="lng{{wbNewId()}}" />
<ul class="nav nav-tabs" id="{{_var.lid}}" role="tablist">
    <wb-foreach wb-from='lang'>
    <li class="nav-item">
        <a class="nav-link" id="{{_var.lid}}-{{_key}}-tab" data-toggle="tab" href="#{{_var.lid}}-{{_key}}" role="tab" aria-selected="true">
               {{_key}}
        </a>
    </li>
    </wb-foreach>
</ul>
<div class="tab-content pt-4">
    <wb-foreach wb-from='lang'>
        <div class="tab-pane fade wb-multilang-row" id="{{_var.lid}}-{{_key}}" data-id="{{_key}}" data-lang="{{_key}}" role="tabpanel" aria-labelledby="{{_var.lid}}-{{_key}}-tab">
            <wb-data wb-field="data">
                
            </wb-data>
        </div>
    </wb-foreach>
</div>
<textarea type='json' class='wb-multilang-data' style='display:none;'></textarea>
</html>
