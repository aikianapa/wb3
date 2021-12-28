<html>
<view head>
    {{ html_entity_decode( {{code}} ) }}
</view>
<edit header="{{_lang.header}}">
    <div>
        <wb-module wb="module=yonger&mode=edit&block=common.inc" />
    </div>
    <meta wb="module=codemirror" name="code">
    <wb-lang>
    [ru]
    header = "Вставка кода в head"
    [en]
    header = "Insert code in head"
</wb-lang>
</edit>
</html>