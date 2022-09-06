<html wb-allow="admin">
<div class="form-group row">
    <label class="form-control-label col-sm-4">Защита доступа к API</label>
    <div class="col-sm-8">
        <wb-module wb="module=switch" name="active" />
    </div>
</div>
<div class="form-group row">
    <label class="form-control-label col-sm-4">Режимы свободного доступа</label>
    <div class="col-sm-8">
        <wb-module wb="module=tagsinput" name="allow" />
    </div>
</div>

<div class="form-group row">
    <label class="col-lg-4 form-control-label">Постоянные токены</label>
    <div class="col-lg-8">
        <wb-multiinput name="tokens">
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text p-1 cursor-pointer">
                    <img src="/module/myicons/24/3b6998/key-circle.1.svg">
                </span>
            </div>
            <input type="text" class="form-control" name="tokens">
        </div>
        </wb-multiinput>
    </div>
</div>
</html>