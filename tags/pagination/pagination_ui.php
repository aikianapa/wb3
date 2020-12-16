<nav class="col-xs-12 col-12">
<ul class="pagination justify-content-center mb-40">
    <li class="page-item" data-page="prev">
        <a href="#prev" class="page-link" href tabindex="-1">&laquo;</a>
    </li>

    <wb-foreach wb="from=pages&tpl=false">
        <li class="page-item">
            <a class="page-link" href="#{{page}}" data-page='{{page}}'>{{page}}</a>
        </li>
    </wb-foreach>

    <li class="page-item" data-page="next">
        <a href="#next" class="page-link" href tabindex="-1">&raquo;</a>
    </li>
    <li class="page-more" data-page="more">
        <a href="#more" class="page-link" href tabindex="-1">{{_lang.more}}</a>
    </li>
</ul>

<script type='wbapp'>
    wbapp.loadScripts(['/engine/tags/pagination/pagination.js'],'pagination-js');
</script>

<wb-lang>
[en]
  more = "More"
[ru]
  more = "Далее"
</wb-lang>
</nav>
