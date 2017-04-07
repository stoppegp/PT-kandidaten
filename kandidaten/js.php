<script type="text/javascript">
jQuery(document).ready(function($){
    var c0 = 0;
    $(".pt-kandidaten-wkf").each(function() {
        c0++;
        var wahl = $(this).data("wahl");
        var startstring = $(this).data("start");
        var container = $(this);
        var selcounter = 0;

        var text_nok = $(this).data("text-nok");
        var text_uu = $(this).data("text-uu");
        var text_nouu = $(this).data("text-nouu");

        var start = orte[wahl];            
        if ('undefined' !== typeof startstring) {
            var startat = startstring.split(",");
            $.each(startat, function(key, val) {
                console.log(key);
                console.log(typeof start[val]);
                if ('object' === typeof start[val]) start = start[val];
            })
        }

    
        var select = document.createElement("select");
        select.id = "wkf" + c0 + "sel" + selcounter;
        container.append(select);
        container.children("#wkf" + c0 + "sel" + selcounter).data("c", selcounter);
        container.children("#wkf" + c0 + "sel" + selcounter).addClass("wkf_sel" + selcounter);

        var keys = Object.keys(start);
        var i, len = keys.length;
        keys.sort();
        container.children(".wkf_sel" + selcounter).append("<option>– Gebiet auswählen –</option>");
        for (i = 0; i < len; i++) {
            key = keys[i];
            container.children(".wkf_sel" + selcounter).append("<option>" + key + "</option>");
        }

        var subfun = function(wahl, container, counter, sub, value) {
            var ncounter = counter+1;
            container.children("*:gt(" + counter + ")").remove();
            if (typeof sub === 'object') {
                var select = document.createElement("select");
                select.id = "wkf" + c0 + "sel" + ncounter;
                container.append(select);
                container.children("#wkf" + c0 + "sel" + ncounter).data("c", ncounter);
                container.children("#wkf" + c0 + "sel" + ncounter).addClass("wkf_sel" + ncounter);
                var keys = Object.keys(sub);
                var i, len = keys.length;
                keys.sort();
                container.children(".wkf_sel" + ncounter).append("<option>– Gebiet auswählen –</option>");
                for (i = 0; i < len; i++) {
                    key = keys[i];
                    container.children(".wkf_sel" + ncounter).append("<option>" + key + "</option>");
                }
                container.children(".wkf_sel" + ncounter).change(function() {
                    subfun(wahl, container, ncounter, sub[this.value], this.value);
                });
            } else if ('string' === typeof sub) {
                var result = document.createElement("p");
                result.id = "wkf" + c0 + "result";
                container.append(result);
                container.children("#wkf" + c0 + "result").addClass("wkf_result");
                if ('object' == typeof kandidaten[wahl][sub]) {
                    if ('undefined' != typeof kandidaten[wahl][sub]['uu'][wahl]) {
                        var content = text_uu;
                        content = content.replace("{wknr}", sub).replace("{wkname}", value).replace("{kandidat}", kandidaten[wahl][sub]['name']).replace("{uu}", kandidaten[wahl][sub]['uu'][wahl]);
                    } else {
                        var content = text_nouu;
                        content = content.replace("{wknr}", sub).replace("{wkname}", value).replace("{kandidat}", kandidaten[wahl][sub]['name']);
                    }
                } else {
                    var content = text_nok;
                    content = content.replace("{wknr}", sub).replace("{wkname}", value);
                }
                container.children("#wkf" + c0 + "result").html(content);
            }

        }

        container.children(".wkf_sel" + selcounter).change(function() {
            subfun(wahl, container, selcounter, start[this.value], this.value);
        });
    });
});


</script>
