<div class='pagination-container'>
    {{#if pagination}}
    {{#if pages - 1}}
    {{#if @last===@index}}
    <ul class="pagination mg-b-0 mt-3">
        {{#each pagination}}
        {{#if this.label=="prev" }}
        <li class="page-item">
            <a class="page-link page-link-icon" data-page="{{this.page}}" href="#"><i
                    class="fa fa-chevron-left"></i></a>
        </li>
        {{elseif this.label == "next"}}
        <li class="page-item">
            <a class="page-link page-link-icon" data-page="{{this.page}}" href="#"><i
                    class="fa fa-chevron-right"></i></a>
        </li>
        {{else}}
        <li class="page-item">
            <a class="page-link" data-page="{{this.page}}" href="#">{{this.label}}</a>
        </li>
        {{/if}}
        {{/each}}
    </ul>
    {{/if}}
    {{/if}}
    {{/if}}
    <script type='wbapp'>
        wbapp.loadScripts(['/engine/tags/pagination/pagination.js'],'pagination-js');
    </script>
</div>