<html>
<wb-var lid="lng{{wbNewId()}}" />
<ul class="nav nav-tabs" id="{{_var.lid}}" role="tablist">
    <wb-foreach wb-from='lang'>
    <li class="nav-item">
        <a class="nav-link" id="{{_var.lid}}-{{id}}-tab" data-toggle="tab" href="#{{_var.lid}}-{{id}}" role="tab" aria-selected="true">
               {{lang}}
        </a>
    </li>
    </wb-foreach>
</ul>
<div class="tab-content pt-4">
    <wb-foreach wb-from='lang'>
        <div class="tab-pane fade wb-multilang-row" id="{{_var.lid}}-{{id}}" data-id="{{id}}" data-lang="{{lang}}" role="tabpanel" aria-labelledby="{{_var.lid}}-{{id}}-tab">
            <wb-data wb-field="data">
                
            </wb-data>
        </div>
    </wb-foreach>
</div>
<textarea type='json' class='wb-multilang-data' style='display:none;'></textarea>
</html>
