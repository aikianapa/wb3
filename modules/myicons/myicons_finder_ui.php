<html>
<wb-var id="{{wbNewId()}}" />
<div id="{{_var.id}}" wb-off>
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text py-0 cursor-pointer" on-click="open">
                <svg class="mi " wb-on size="24" stroke="333333" wb-module="myicons"></svg>
            </span>
        </div>
        <input class="form-control" name type="text" on-keyup="find" autocomplete="off">
    </div>
    <div class="off-canvas off-canvas-overlay off-canvas-right px-1 ht-100v">
        <span class="r-10 t-10 pos-absolute cursor-pointer" on-click="close">
            <svg class="mi mi-interface-essential-109" wb-on size="24" stroke="333333" wb-module="myicons"></svg>
        </span>
        <div class="p-2 tx-gray-700">
            <input type="search" class="form-control" on-keyup="find" placeholder="Поиск..." autocomplete="off">
        </div>
        <div class="list d-none scroll-y pb-3 text-center" style="height: calc(100vh - 50px);">
            {{#each list}}
            <div class="d-inline p-2 text-center" data-id="{{@key}}" on-click="select">
                {{{svg}}}
            </div>
            {{/each}}
        </div>
    </div>
</div>
<script>
(()=>{
    if (document.getElementById('{{_var.id}}').done == true) {
        return
    } else {
        document.getElementById('{{_var.id}}').done = true
    }
    let myicofind = new Ractive({
        el: "#{{_var.id}}",
        template: $("#{{_var.id}}").html(),
        data: [],
        on: {
            complete(ev) {
                let val = $(myicofind.el).find("input[name]").val()
                if (val > "") {
                    $(myicofind.el).find(".input-group-prepend > span").html('<img src="/module/myicons/24/333333/'+val+'.svg" width="24" height="24">')
                }
            },
            open(ev) {
                $("#{{_var.id}} .off-canvas").addClass("show")
                $("#{{_var.id}} input[type=search]").val("")
            },
            close(ev) {
                $(myicofind.el).find(".off-canvas").removeClass("show")
            },
            find(ev) {
                let str = $(ev.node).val() + "";
                let len = str.length;
                if (len > 0) {
                    void(0)
                } else {
                    myicofind.set("list", [])
                    return
                }
                if (ev.event.key !== "Enter" && ev.event.type !== "click") return
                $("#{{_var.id}} .list").addClass("d-none")
                if (!$(myicofind.el).find(".off-canvas").hasClass("show")) {
                    $(myicofind.el).find(".off-canvas").addClass("show")
                    $("#{{_var.id}} input[type=search]").val(str)
                }
                wbapp.post("/module/myicons/getlist", {
                    find: str
                }, function(data) {
                    myicofind.set("list", data)
                    myicofind.updateModel()
                    $("#{{_var.id}} .list svg").attr("style", "height:30px;width:30px;")
                    $("#{{_var.id}} .list").removeClass("d-none")
                    $("#{{_var.id}} input[type=search]").focus()
                })
                return false
            },
            select(ev) {
                let name = $(ev.node).data("id");
                let svg = $(ev.node).find("svg")[0];
                $(myicofind.el).find(".input-group-prepend > span").html($(svg).outer())
                $(myicofind.el).find("input[name]").val(name).attr("value", name).trigger("change")
                $(myicofind.el).find(".off-canvas").removeClass("show")
            }
        }
    })
})()
</script>

</html>