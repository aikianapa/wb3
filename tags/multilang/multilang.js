function wb_multilang() {

    var ml_value = function(that, data = undefined) {
        if (data == undefined) {
            var data = $(that).children("textarea").html();
            if (data !== undefined) data = json_decode(data, true);
            return data;
        } else {
            $(that).children("textarea").text(json_encode(data));
            $(that).children("textarea").trigger("change");
        }
    }

    var ml_store = function(that) {
        var data = {};
        var name = $(that).attr("name");
        $(that).children(".tab-content").children(".wb-multilang-row").each(async function(i) {
            var multi = $(this).clone();
            var item = {};
            var lid = $(multi).attr("data-id");
            var lang = $(multi).attr("data-lang");
            if (lid == undefined || lid == "") {
                $(multi).remove();
            } else {
                $(multi).find(".wb-multiinput-row").each(function() {
                    var name = $(this).attr("name");
                    var txtd = $(this).children(".wb-multiinput-data");

                    $(txtd).attr("wb-name", name).removeAttr("name");
                    $(multi).append($(txtd));
                    $(this).remove();
                });
                $(multi).find(":input[name]").each(function() {
                    var name = $(this).attr("name");
                    $(this).attr("wb-name", name).removeAttr("name");
                });
                var inputs = $(multi).find("input:not(.wb-unsaved),select:not(.wb-unsaved),textarea:not(.wb-unsaved)");
                if ($(inputs).length == 1 && $(inputs).is("input")) {
                    mono = true;
                }

                $(inputs).each(async function() {
                    var value = $(this).jsonVal();
                    if ($(this).attr("name") !== undefined) {
                        var field = $(this).attr("name");
                        $(this).attr("wb-name", field).removeAttr("name");
                    } else if ($(this).attr("wb-name") !== undefined) {
                        var field = $(this).attr("wb-name");
                    }
                    if (field !== undefined) {
                        item[field] = value;
                    }
                    if ($(this).is('input[type=checkbox]')) {
                        if ($(this).prop('checked') == true) {
                            item[field] = "on";
                        } else {
                            item[field] = "";
                        }
                    }
                    if ($(this).is('input[type=radio]:checked')) {
                        item[field] = value;
                    }
                    $(this).attr("wb-name", name).removeAttr("name");
                });
                data[lid] = item;
            }
            ml_value(that, data)
        });
    }

    $(document).find("wb-multilang").each(async function() {
        if (this.done == undefined) {
            if ($(this).attr('name') == undefined) $(this).attr('name', 'lang')
            var that = this;

            $(that).find('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                window.dispatchEvent(new Event('resize'));
                // fix for jodit
            });

            $(that).delegate(".wb-multilang-row :input", "change blur", async function() {
                if ($(this).is("select")) {
                    $(this).find("option:not(:selected)").prop("selected", false).removeAttr("selected");
                    $(this).find("option:selected").prop("selected", true).attr("selected", true);
                }
                ml_store(that);
            });
            ml_store(that);
            $(this).find("input:visible:first").trigger("change"); // important
            $(this).removeAttr("name");
            wbapp.init();
            this.done = true;
        }
    });
}

$(document).on('multilang-js', async function() {
    wb_multilang();
    $(document).find('[data-remove="multilang-js"]').remove();
})