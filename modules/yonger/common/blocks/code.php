<html>
<view head>
    {{ html_entity_decode( {{code}} ) }}
</view>
<edit header="{{_lang.header}}">
    <div>
        <wb-module wb="module=yonger&mode=edit&block=common.inc" />
    </div>
    <textarea name="code" class="wd-100p" rows="30"></textarea>
    <wb-lang>
    [ru]
    header = "Вставка кода в head"
    [en]
    header = "Insert code in head"
</wb-lang>
</edit>
</html>